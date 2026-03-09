<?php

namespace App\Services;

use App\Models\Classroom;
use App\Models\Course;
use App\Models\Schedule;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\TeacherAvailability;
use App\Models\TeacherSubjectAssignment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use App\Services\PortalAccessService;

class TimetableGeneratorService
{
    // the hardcoded defaults are retained solely for migration/seeding or
    // configuration bootstrap, but most methods will use the access service.
    private const DAYS = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
    private const SLOT_BLOCKS = [
        ['09:00', '10:00'],
        ['10:00', '11:00'],
        ['11:00', '12:00'],
        ['12:00', '13:00'],
        ['14:00', '15:00'],
        ['15:00', '16:00'],
    ];
    private const SUBJECTS_PER_SEMESTER = 8;
    private ?array $subjectSchema = null;

    public function __construct(private PortalAccessService $accessService)
    {
    }

    public function generate(array $payload): array
    {
        ini_set('max_execution_time', '600'); // 10 minutes
        ini_set('memory_limit', '1024M');

        $course = Course::findOrFail((int) $payload['course_id']);
        $academicYear = (int) $payload['academic_year'];
        $clearExisting = (bool) ($payload['clear_existing'] ?? false);
        $subjectIds = collect($payload['subject_ids'] ?? [])->map(fn ($id) => (int) $id)->filter()->values();
        $subjectTeacherMap = collect($payload['subject_teacher_map'] ?? [])
            ->mapWithKeys(fn ($teacherId, $subjectId) => [(int) $subjectId => (int) $teacherId]);

        [$fromSemester, $toSemester] = $this->semesterRangeForYear($course, $academicYear);
        $targetSemesters = $this->resolveTargetSemesters($course, $academicYear, $payload['semester_id'] ?? null);
        $semesterIds = $targetSemesters->pluck('id')->filter()->values();
        $semesterNumbers = $targetSemesters->pluck('semester_number')->filter()->unique()->values();
        if ($semesterNumbers->isEmpty()) {
            $semesterNumbers = collect(range($fromSemester, $toSemester));
            $targetSemesters = $semesterNumbers
                ->map(fn (int $number) => (object) ['id' => null, 'semester_number' => $number])
                ->values();
        }

        $subjectsQuery = Subject::query()
            ->where('course_id', $course->id)
            ->whereIn('semester_sequence', $semesterNumbers)
            ->when($subjectIds->isNotEmpty(), fn ($q) => $q->whereIn('id', $subjectIds));

        if ($this->hasSubjectColumn('weekly_hours')) {
            $subjectsQuery->orderByDesc('weekly_hours');
        }

        $subjects = $subjectsQuery->orderBy('id')->get();

        if ($subjects->isEmpty()) {
            throw new RuntimeException('No subjects found for the selected course/year.');
        }

        $this->assertSemesterSubjectLimit($subjects, $semesterNumbers);

        $assignments = TeacherSubjectAssignment::query()
            ->whereIn('subject_id', $subjects->pluck('id'))
            ->orderByDesc('assigned_date')
            ->orderByDesc('id')
            ->get()
            ->groupBy('subject_id');

        $rooms = Classroom::query()->orderBy('name')->get();
        if ($rooms->isEmpty()) {
            throw new RuntimeException('No classrooms available.');
        }

        $teacherIds = $assignments->flatten(1)->pluck('teacher_id')
            ->merge($subjectTeacherMap->values())
            ->unique()
            ->values();
        $availabilities = TeacherAvailability::query()
            ->whereIn('teacher_id', $teacherIds)
            ->get()
            ->groupBy('teacher_id');

        return DB::transaction(function () use (
            $payload,
            $clearExisting,
            $semesterIds,
            $targetSemesters,
            $course,
            $academicYear,
            $fromSemester,
            $toSemester,
            $subjects,
            $assignments,
            $subjectTeacherMap,
            $rooms,
            $availabilities
        ) {
            if ($clearExisting) {
                $this->classScheduleQuery($course, $academicYear)->delete();
            }

            $existingSchedules = $clearExisting
                ? Schedule::query()
                    ->whereDoesntHave('subject', function ($query) use ($course, $fromSemester, $toSemester) {
                        $query->where('course_id', $course->id)
                            ->whereBetween('semester_sequence', [$fromSemester, $toSemester]);
                    })
                    ->get()
                : Schedule::query()->get();

            $state = $this->buildInitialState($existingSchedules);
                $generated = [];
                $failures = [];

            foreach ($subjects as $subject) {
                $teacherId = (int) ($subjectTeacherMap->get($subject->id) ?: optional($assignments->get($subject->id)?->first())->teacher_id);
                if (!$teacherId) {
                    $failures[] = "Subject {$subject->name}: no active teacher assignment.";
                    continue;
                }

                $semester = $targetSemesters->firstWhere('semester_number', (int) $subject->semester_sequence);
                $semesterId = $semester?->id;
                $semesterKey = $semesterId ? "sem:{$semesterId}" : "seq:{$subject->semester_sequence}";

                $requiredHours = max(1, (int) (($subject->weekly_hours ?? null) ?: $subject->credits ?: 4));
                $blockSize = 1; // Fixed 1-hour slots for all classes

                $remaining = $requiredHours;
                $guard = 0;
                while ($remaining > 0) {
                    $guard++;
                    if ($guard > 2000) {
                        $failures[] = "Subject {$subject->name}: scheduling loop guard triggered.";
                        break;
                    }

                    $candidate = $this->findCandidate(
                        $subject,
                        $teacherId,
                        $semesterKey,
                        $rooms->pluck('id')->all(),
                        min($blockSize, $remaining),
                        $state,
                        $availabilities->get($teacherId, collect())
                    );

                    if (!$candidate) {
                        $failures[] = "Subject {$subject->name}: unable to place remaining {$remaining} hour(s) without conflicts.";
                        break;
                    }

                    foreach ($candidate['blocks'] as $blockIndex) {
                        $slot = $this->slotBlocks()[$blockIndex];
                        $record = [
                            'semester_id' => $semesterId,
                            'subject_id' => $subject->id,
                            'teacher_id' => $teacherId,
                            'classroom_id' => $candidate['room_id'],
                            'day_of_week' => $candidate['day'],
                            'start_time' => $slot[0],
                            'end_time' => $slot[1],
                        ];

                        Schedule::create($record);
                        $generated[] = $record;
                        $this->occupyState($state, $candidate['day'], $blockIndex, $teacherId, $candidate['room_id'], $semesterKey, $subject->id);
                    }

                    $remaining -= count($candidate['blocks']);
                }
            }

            if (!empty($failures)) {
                throw new RuntimeException("Timetable generation failed:\n- " . implode("\n- ", $failures));
            }

            return [
                'generated_count' => count($generated),
                'cleared' => $clearExisting,
                'course_id' => (int) $payload['course_id'],
                'academic_year' => (int) $payload['academic_year'],
            ];
        });
    }

    private function resolveTargetSemesters(Course $course, int $year, ?int $semesterId): Collection
    {
        if ($semesterId) {
            $semester = Semester::query()
                ->where('id', $semesterId)
                ->where('course_id', $course->id)
                ->first();

            return $semester ? collect([$semester]) : collect();
        }

        [$from, $to] = $this->semesterRangeForYear($course, $year);

        return Semester::query()
            ->where('course_id', $course->id)
            ->whereBetween('semester_number', [$from, $to])
            ->orderBy('semester_number')
            ->get();
    }

    private function semesterRangeForYear(Course $course, int $year): array
    {
        $semestersPerYear = max(1, (int) $course->semesters_per_year);
        $from = (($year - 1) * $semestersPerYear) + 1;
        $to = $year * $semestersPerYear;
        return [$from, $to];
    }

    private function classScheduleQuery(Course $course, int $year)
    {
        [$from, $to] = $this->semesterRangeForYear($course, $year);

        return Schedule::query()->whereHas('subject', function ($query) use ($course, $from, $to) {
            $query->where('course_id', $course->id)
                ->whereBetween('semester_sequence', [$from, $to]);
        });
    }

    private function buildInitialState(Collection $existingSchedules): array
    {
        $state = [
            'teacher' => [],
            'room' => [],
            'semester' => [],
            'subject' => [],
            'day_load' => [],
            'subject_day_count' => [],
        ];

        foreach ($existingSchedules as $row) {
            $semesterKey = $row->semester_id ? "sem:{$row->semester_id}" : 'sem:null';
            $day = $row->day_of_week;
            foreach ($this->blockIndexesForRange($row->start_time, $row->end_time) as $blockIndex) {
                $this->occupyState($state, $day, $blockIndex, (int) $row->teacher_id, (int) $row->classroom_id, $semesterKey, (int) $row->subject_id);
            }
        }

        return $state;
    }

    private function findCandidate(
        Subject $subject,
        int $teacherId,
        string $semesterKey,
        array $roomIds,
        int $blockSize,
        array $state,
        Collection $teacherAvailabilities
    ): ?array {
        $dayOrder = collect($this->days())
            ->sortBy(fn ($day) => $state['subject_day_count'][$subject->id][$day] ?? 0)
            ->values();

        foreach ($dayOrder as $day) {
            for ($startBlock = 0; $startBlock <= count($this->slotBlocks()) - $blockSize; $startBlock++) {
                $blocks = range($startBlock, $startBlock + $blockSize - 1);

                if ($this->wouldCreateConsecutiveSubject($subject->id, $semesterKey, $day, $blocks, $state)) {
                    continue;
                }

                if (!$this->teacherBlocksAvailable($teacherId, $day, $blocks, $teacherAvailabilities, $state)) {
                    continue;
                }

                if (!$this->semesterBlocksAvailable($semesterKey, $day, $blocks, $state)) {
                    continue;
                }

                foreach ($roomIds as $roomId) {
                    if ($this->roomBlocksAvailable($roomId, $day, $blocks, $state)) {
                        return [
                            'day' => $day,
                            'room_id' => $roomId,
                            'blocks' => $blocks,
                        ];
                    }
                }
            }
        }

        return null;
    }

    private function teacherBlocksAvailable(int $teacherId, string $day, array $blocks, Collection $teacherAvailabilities, array $state): bool
    {
        foreach ($blocks as $blockIndex) {
            if (($state['teacher'][$teacherId][$day][$blockIndex] ?? false) === true) {
                return false;
            }
        }

        if ($teacherAvailabilities->isEmpty()) {
            return true;
        }

        foreach ($blocks as $blockIndex) {
            [$start, $end] = $this->slotBlocks()[$blockIndex];
            $isAvailable = $teacherAvailabilities
                ->where('day_of_week', $day)
                ->contains(function ($availability) use ($start, $end) {
                    return $availability->start_time <= $start . ':00' && $availability->end_time >= $end . ':00';
                });

            if (!$isAvailable) {
                return false;
            }
        }

        return true;
    }

    private function semesterBlocksAvailable(string $semesterKey, string $day, array $blocks, array $state): bool
    {
        foreach ($blocks as $blockIndex) {
            if (($state['semester'][$semesterKey][$day][$blockIndex] ?? false) === true) {
                return false;
            }
        }
        return true;
    }

    private function roomBlocksAvailable(int $roomId, string $day, array $blocks, array $state): bool
    {
        foreach ($blocks as $blockIndex) {
            if (($state['room'][$roomId][$day][$blockIndex] ?? false) === true) {
                return false;
            }
        }
        return true;
    }

    private function wouldCreateConsecutiveSubject(int $subjectId, string $semesterKey, string $day, array $blocks, array $state): bool
    {
        $min = min($blocks);
        $max = max($blocks);
        $before = $min - 1;
        $after = $max + 1;

        if ($before >= 0 && (($state['subject'][$subjectId][$semesterKey][$day][$before] ?? false) === true)) {
            return true;
        }

        if ($after < count($this->slotBlocks()) && (($state['subject'][$subjectId][$semesterKey][$day][$after] ?? false) === true)) {
            return true;
        }

        return false;
    }

    private function occupyState(array &$state, string $day, int $blockIndex, int $teacherId, int $roomId, string $semesterKey, int $subjectId): void
    {
        $state['teacher'][$teacherId][$day][$blockIndex] = true;
        $state['room'][$roomId][$day][$blockIndex] = true;
        $state['semester'][$semesterKey][$day][$blockIndex] = true;
        $state['subject'][$subjectId][$semesterKey][$day][$blockIndex] = true;
        $state['day_load'][$semesterKey][$day] = ($state['day_load'][$semesterKey][$day] ?? 0) + 1;
        $state['subject_day_count'][$subjectId][$day] = ($state['subject_day_count'][$subjectId][$day] ?? 0) + 1;
    }

    private function blockIndexesForRange(string $start, string $end): array
    {
        $indexes = [];
        foreach ($this->slotBlocks() as $i => $block) {
            [$bStart, $bEnd] = $block;
            if ($start < $bEnd . ':00' && $end > $bStart . ':00') {
                $indexes[] = $i;
            }
        }
        return $indexes;
    }

    private function hasSubjectColumn(string $column): bool
    {
        if ($this->subjectSchema === null) {
            $this->subjectSchema = [
                'is_lab' => Schema::hasColumn('subjects', 'is_lab'),
                'weekly_hours' => Schema::hasColumn('subjects', 'weekly_hours'),
                'lab_block_hours' => Schema::hasColumn('subjects', 'lab_block_hours'),
            ];
        }

        return (bool) ($this->subjectSchema[$column] ?? false);
    }

    private function assertSemesterSubjectLimit(Collection $subjects, Collection $semesterNumbers): void
    {
        $grouped = $subjects->groupBy(fn ($subject) => (int) $subject->semester_sequence);
        $requiredPerSemester = max(1, (int) config('timetable.subjects_per_semester', self::SUBJECTS_PER_SEMESTER));

        foreach ($semesterNumbers as $semesterNumber) {
            $count = $grouped->get((int) $semesterNumber, collect())->count();
            if ($count !== $requiredPerSemester) {
                throw new RuntimeException(
                    "Semester {$semesterNumber} has {$count} subjects. Exactly {$requiredPerSemester} subjects are required for timetable generation."
                );
            }
        }
    }

    private function days(): array
    {
        return $this->accessService->workingDays();
    }

    private function slotBlocks(): array
    {
        $blocks = config('timetable.slot_blocks', self::SLOT_BLOCKS);
        return array_slice($blocks, 0, $this->accessService->slotsPerDay());
    }
}
