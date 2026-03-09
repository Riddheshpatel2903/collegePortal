<?php

namespace App\Services;

use App\Models\Classroom;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherSubjectAssignment;
use App\Models\Timetable;
use App\Repositories\TimetableRepository;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use App\Services\PortalAccessService;

class AutoTimetableService
{
    // constants remain for compatibility or seeding but most logic will call
    // helper methods below which consult the access service, allowing runtime
    // overrides.
    private const DAYS = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
    private const SLOTS = [1, 2, 3, 4, 5, 6];

    /**
     * Working days returns the currently configured weekday sequence.
     * Retained for compatibility; most callers should use this method
     * rather than the `DAYS` constant directly so that runtime changes
     * via the portal settings take effect.
     */
    private function days(): array
    {
        return $this->accessService->workingDays();
    }

    /**
     * Numeric slot indexes respecting the configured slots-per-day.
     */
    private function slots(): array
    {
        $count = $this->accessService->slotsPerDay();
        return range(1, $count);
    }
    private const WEEKLY_SLOT_TARGET = 30;
    private const SLOT_RETRY_GUARD = 2000;
    private const FULL_GENERATION_RETRIES = 5;

    public function __construct(
        private TimetableRepository $repository,
        private TimetableConflictValidator $conflictValidator,
        private TimetableRetryEngine $retryEngine,
        private PortalAccessService $accessService
    ) {
    }

    public function generationContext(int $courseId, string $semesterType): array
    {
        $course = $this->repository->findCourseOrFail($courseId);
        $years = $this->repository->yearsForCourse($course)->values();
        $this->repository->ensureFixedLectureClassrooms($course->id, $years->all());
        $this->repository->ensureLabClassrooms($course->id, 2);
        $this->optimizeFixedLectureRoomsForSemester($course->id, $years, strtolower($semesterType));
        $semesterNumbers = $this->repository->semesterNumbersByType($course, $semesterType)->all();

        $lectureRooms = $years->mapWithKeys(fn($year) => [
            (int) $year => $this->repository->exactLectureClassroomForCourseYear($course->id, (int) $year),
        ]);

        return [
            'course' => $course,
            'years' => $years,
            'teachers' => $this->repository->departmentTeachers((int) $course->department_id),
            'subjects' => $this->repository->courseSubjectsForSemesters($course->id, $semesterNumbers),
            'lecture_rooms' => $lectureRooms,
            'lab_rooms' => $this->repository->labClassrooms($course->id),
            'semester_type' => strtolower($semesterType),
        ];
    }

    public function generate(array $payload): array
    {
        ini_set('max_execution_time', '300');
        ini_set('memory_limit', '512M');

        $course = $this->repository->findCourseOrFail((int) $payload['course_id']);
        $allYears = $this->repository->yearsForCourse($course)->values();
        $selectedYears = collect($payload['selected_years'] ?? $allYears->all())
            ->map(fn($year) => (int) $year)
            ->filter(fn(int $year) => $allYears->contains($year))
            ->unique()
            ->values();

        if ($selectedYears->isEmpty()) {
            throw ValidationException::withMessages(['selected_years' => 'At least one class year must be selected.']);
        }

        // Persist room provisioning outside generation transaction so failures don't rollback room setup.
        $this->repository->ensureFixedLectureClassrooms($course->id, $selectedYears->all());
        $this->repository->ensureLabClassrooms($course->id, 2);
        $this->optimizeFixedLectureRoomsForSemester($course->id, $selectedYears, strtolower((string) $payload['semester_type']));

        return DB::transaction(function () use ($payload) {
            $course = $this->repository->findCourseOrFail((int) $payload['course_id']);
            $semesterType = strtolower((string) $payload['semester_type']);
            $allYears = $this->repository->yearsForCourse($course)->values();
            $selectedYears = collect($payload['selected_years'] ?? $allYears->all())
                ->map(fn($year) => (int) $year)
                ->filter(fn(int $year) => $allYears->contains($year))
                ->unique()
                ->values();

            $selectedTeacherIds = collect($payload['selected_teacher_ids'] ?? [])
                ->map(fn($id) => (int) $id)
                ->filter()
                ->unique()
                ->values();

            $selectedClassroomIds = collect($payload['selected_classroom_ids'] ?? [])
                ->map(fn($id) => (int) $id)
                ->filter()
                ->unique()
                ->values();

            $semesterNumbers = $this->repository->semesterNumbersByType($course, $semesterType)->all();
            $subjects = $this->repository->courseSubjectsForSemesters($course->id, $semesterNumbers);
            if ($subjects->isEmpty()) {
                throw new RuntimeException('No subjects found for the selected course and semester type.');
            }

            $teacherMap = $this->repository->teacherMapForSubjects($subjects->pluck('id'));
            $activeTeachers = $this->repository->departmentTeachers(
                (int) $course->department_id,
                $selectedTeacherIds->isEmpty() ? null : $selectedTeacherIds->all()
            );
            $activeTeacherIds = $activeTeachers->pluck('id')->map(fn($id) => (int) $id)->all();

            $filteredSubjects = $subjects
                ->filter(function (Subject $subject) use ($selectedYears) {
                    $year = $this->yearFromSemester((int) ($subject->semester_number ?? $subject->semester_sequence ?? 1));
                    return $selectedYears->contains($year);
                })
                ->values();

            if ($filteredSubjects->isEmpty()) {
                throw new RuntimeException('No schedulable subjects left after teacher/class filters.');
            }

            $lectureRoomsByYear = [];
            foreach ($selectedYears as $year) {
                $room = $this->repository->exactLectureClassroomForCourseYear($course->id, (int) $year);
                if (!$room) {
                    throw new RuntimeException("No fixed lecture classroom assigned for Year {$year}.");
                }
                $lectureRoomsByYear[(int) $year] = $room;
            }

            $labRooms = $this->repository->labClassrooms($course->id);
            if ($selectedClassroomIds->isNotEmpty()) {
                $labRooms = $labRooms->filter(fn($room) => $selectedClassroomIds->contains((int) $room->id))->values();
            }
            if ($filteredSubjects->contains(fn(Subject $subject) => $this->isLabSubject($subject)) && $labRooms->isEmpty()) {
                throw new RuntimeException('No selectable lab classrooms found for lab subjects.');
            }

            $this->repository->clearCourseSemesterTypeYears($course->id, $semesterType, $selectedYears->all());
            $subjectTeacherCandidates = $this->buildSubjectTeacherCandidates(
                $filteredSubjects,
                $teacherMap,
                $activeTeacherIds
            );

            $baselineRows = $this->repository->otherTimetableBySemesterType($semesterType)
                ->merge($this->repository->timetableByCourseAndSemesterType($course->id, $semesterType));
            $baselineState = $this->buildStateFromExisting($baselineRows);

            $teacherAvailabilityByTeacher = $this->repository
                ->teacherAvailabilities($activeTeacherIds)
                ->groupBy('teacher_id')
                ->map(fn($availabilities) => $availabilities->groupBy('day_of_week'));

            $generated = $this->retryEngine->run(self::FULL_GENERATION_RETRIES, function () use ($selectedYears, $semesterType, $filteredSubjects, $subjectTeacherCandidates, $course, $labRooms, $lectureRoomsByYear, $baselineState, $teacherAvailabilityByTeacher) {
                return $this->attemptGeneration(
                    $selectedYears,
                    $semesterType,
                    $filteredSubjects,
                    $subjectTeacherCandidates,
                    $course->id,
                    $labRooms,
                    $lectureRoomsByYear,
                    $baselineState,
                    $teacherAvailabilityByTeacher->toArray()
                );
            });

            $rows = $generated['rows'] ?? [];
            $this->repository->bulkInsert($rows);

            return [
                'generated_count' => count($rows),
                'course_id' => $course->id,
                'semester_type' => $semesterType,
                'selected_years' => $selectedYears->all(),
            ];
        });
    }

    public function editableGrid(int $courseId, string $semesterType): array
    {
        $course = $this->repository->findCourseOrFail($courseId);
        $rows = $this->repository->timetableByCourseAndSemesterType($courseId, $semesterType);
        $years = $rows->pluck('year_number')->unique()->sort()->values();
        if ($years->isEmpty()) {
            $years = $this->repository->yearsForCourse($course)->values();
        }

        $grid = [];
        foreach ($years as $year) {
            foreach ($this->days() as $day) {
                foreach ($this->slots() as $slot) {
                    $grid[(int) $year][$day][$slot] = null;
                }
            }
        }
        foreach ($rows as $row) {
            $grid[(int) $row->year_number][$row->day][(int) $row->slot_number] = $row;
        }

        $classroomMap = [];
        $subjectMap = [];
        foreach ($years as $year) {
            $semesterNumber = $this->repository->semesterForYearByType((int) $year, $semesterType);
            $lectureRoom = $this->repository->exactLectureClassroomForCourseYear($course->id, (int) $year);
            $classroomMap[(int) $year] = $lectureRoom
                ? collect([$lectureRoom])->merge($this->repository->labClassrooms($course->id))
                : $this->repository->labClassrooms($course->id);
            $subjectMap[(int) $year] = $this->repository->subjectOptionsForSemester($course->id, $semesterNumber);
        }

        return [
            'course' => $course,
            'semester_type' => strtolower($semesterType),
            'days' => $this->days(),
            'slots' => $this->slots(),
            'years' => $years,
            'grid' => $grid,
            'teachers' => $this->repository->departmentTeachers((int) $course->department_id),
            'subjects_by_year' => $subjectMap,
            'classrooms_by_year' => $classroomMap,
            'rows_count' => $rows->count(),
        ];
    }

    public function updateSlot(Timetable $row, array $payload): void
    {
        if ($this->accessService->featureEnabled('semester_lock', false)) {
            throw ValidationException::withMessages([
                'generator' => 'Semester lock is enabled. Timetable editing is disabled.',
            ]);
        }

        DB::transaction(function () use ($row, $payload) {
            $semesterType = ((int) $row->semester_number % 2) === 0 ? 'even' : 'odd';
            $isLabBlock = !empty($row->lab_block_id);
            $blockRows = $isLabBlock
                ? $this->repository->rowsByLabBlock((string) $row->lab_block_id)
                : new EloquentCollection([$row]);

            $duration = max(1, $blockRows->count());
            $day = (string) $payload['day'];
            $startSlot = (int) $payload['slot_number'];
            $slots = range($startSlot, $startSlot + $duration - 1);
            if (max($slots) > max($this->slots())) {
                throw ValidationException::withMessages(['slot_number' => 'Selected slot exceeds available day slots.']);
            }

            $subject = Subject::query()->findOrFail((int) $payload['subject_id']);
            if ((int) $subject->course_id !== (int) $row->course_id || (int) $subject->semester_number !== (int) $row->semester_number) {
                throw ValidationException::withMessages(['subject_id' => 'Subject must belong to the same course and semester.']);
            }

            $subjectIsLab = $this->isLabSubject($subject);
            if ($duration > 1 && !$subjectIsLab) {
                throw ValidationException::withMessages(['subject_id' => 'Lab block cannot be converted to lecture by slot edit.']);
            }
            if ($duration === 1 && $subjectIsLab && $this->labDuration($subject) > 1) {
                throw ValidationException::withMessages(['subject_id' => 'Lab subjects require consecutive multi-slot blocks.']);
            }

            $teacherId = (int) $payload['teacher_id'];
            $classroomId = (int) $payload['classroom_id'];
            $ignoreIds = $blockRows->pluck('id')->map(fn($id) => (int) $id)->all();

            if ($subjectIsLab) {
                $classroom = Classroom::query()->findOrFail($classroomId);
                if ($classroom->type !== 'lab') {
                    throw ValidationException::withMessages(['classroom_id' => 'Lab subject must use a lab classroom.']);
                }
            } else {
                $classroom = Classroom::query()->findOrFail($classroomId);
                if ($classroom->type !== 'lecture') {
                    throw ValidationException::withMessages(['classroom_id' => 'Lecture subject must use a lecture classroom.']);
                }
                $fixed = $this->repository->exactLectureClassroomForCourseYear((int) $row->course_id, (int) $row->year_number);
                if (!$fixed || (int) $fixed->id !== $classroomId) {
                    throw ValidationException::withMessages([
                        'classroom_id' => $fixed
                            ? "Lecture subject must use fixed Year {$row->year_number} classroom: {$fixed->name}."
                            : 'No fixed lecture classroom configured for this class year.',
                    ]);
                }
            }

            $teacher = Teacher::query()->findOrFail($teacherId);
            $teacherMax = min(
                max(1, (int) ($teacher->max_lectures_per_day ?? 6)),
                $this->accessService->teacherMaxLecturesPerDay()
            );
            $teacherAvailabilityByTeacher = $this->repository->teacherAvailabilities([$teacherId])
                ->groupBy('teacher_id')
                ->map(fn($availabilities) => $availabilities->groupBy('day_of_week'))
                ->toArray();

            $this->conflictValidator->assertNoConflictsForEdit(
                (int) $row->course_id,
                (int) $row->year_number,
                $teacherId,
                $classroomId,
                $day,
                $slots,
                $semesterType,
                $ignoreIds,
                $teacherMax,
                $teacherAvailabilityByTeacher
            );

            $blockRows = $blockRows->sortBy('slot_number')->values();
            foreach ($blockRows as $index => $slotRow) {
                $slotRow->subject_id = (int) $payload['subject_id'];
                $slotRow->teacher_id = $teacherId;
                $slotRow->classroom_id = $classroomId;
                $slotRow->day = $day;
                $slotRow->slot_number = $slots[$index];
            }

            $this->repository->saveMany($blockRows);
        });
    }

    private function attemptGeneration(
        Collection $selectedYears,
        string $semesterType,
        Collection $filteredSubjects,
        array $subjectTeacherCandidates,
        int $courseId,
        EloquentCollection $labRooms,
        array $lectureRoomsByYear,
        array $baselineState,
        array $teacherAvailabilityByTeacher
    ): array {
        $state = $baselineState;
        $rowsToInsert = [];
        $errors = [];

        foreach ($selectedYears as $year) {
            $semester = $this->repository->semesterForYearByType((int) $year, $semesterType);
            $classSubjects = $filteredSubjects
                ->filter(fn(Subject $subject) => (int) ($subject->semester_number ?? $subject->semester_sequence ?? 0) === $semester)
                ->values();

            $this->assertWeeklyHourTarget($classSubjects, (int) $year, $semester);
            $this->assertInitialCoverageFeasibility(
                $courseId,
                (int) $year,
                $semester,
                $classSubjects,
                $subjectTeacherCandidates,
                $semesterType,
                (int) $lectureRoomsByYear[(int) $year]->id,
                $state
            );

            $labSubjects = $classSubjects
                ->filter(fn(Subject $subject) => $this->isLabSubject($subject))
                ->sortByDesc(fn(Subject $subject) => $this->labDuration($subject) * $this->hoursPerWeek($subject))
                ->values();
            $optionsCache = [];
            $lectureSubjects = $classSubjects
                ->reject(fn(Subject $subject) => $this->isLabSubject($subject))
                ->values();

            $lectureSubjects = $lectureSubjects
                ->sortByDesc(fn(Subject $subject) => $this->hoursPerWeek($subject))
                ->values();

            foreach ($labSubjects as $subject) {
                $hours = $this->hoursPerWeek($subject);
                $duration = $this->labDuration($subject);
                if ($hours % $duration !== 0) {
                    throw new RuntimeException("Lab subject {$subject->name} in Semester {$semester} has {$hours} hours/week, not divisible by lab duration {$duration}.");
                }

                $sessions = (int) ($hours / $duration);
                $placed = 0;
                while ($placed < $sessions) {
                    $ok = $this->tryPlace(
                        $courseId,
                        (int) $year,
                        $semester,
                        $subject,
                        $subjectTeacherCandidates[(int) $subject->id] ?? [],
                        $semesterType,
                        $duration,
                        $labRooms,
                        $state,
                        $rowsToInsert,
                        $teacherAvailabilityByTeacher
                    );
                    if (!$ok) {
                        $errors[] = "Unable to place lab subject {$subject->name} for Year {$year} (Semester {$semester}).";
                        break;
                    }
                    $placed++;
                }
            }

            $lectureRemaining = $lectureSubjects
                ->mapWithKeys(fn(Subject $subject) => [(int) $subject->id => $this->hoursPerWeek($subject)])
                ->all();

            $guard = 0;
            while (collect($lectureRemaining)->sum() > 0) {
                $guard++;
                if ($guard > 2000) {
                    $errors[] = "Unable to complete lecture scheduling for Year {$year} (Semester {$semester}).";
                    break;
                }

                $optionsCache = [];
                $nextSubjects = $lectureSubjects
                    ->filter(fn(Subject $subject) => ($lectureRemaining[(int) $subject->id] ?? 0) > 0)
                    ->sortBy(function (Subject $subject) use ($subjectTeacherCandidates, $courseId, $year, $semesterType, $state, $lectureRoomsByYear, $teacherAvailabilityByTeacher, $lectureRemaining, &$optionsCache) {
                        $teacherId = (int) (($subjectTeacherCandidates[(int) $subject->id][0] ?? 0));
                        $options = $this->estimateSingleSlotOptions(
                            $courseId,
                            (int) $year,
                            $teacherId,
                            (int) $lectureRoomsByYear[(int) $year]->id,
                            $semesterType,
                            $state,
                            $teacherAvailabilityByTeacher,
                            $optionsCache
                        );
                        $remaining = (int) ($lectureRemaining[(int) $subject->id] ?? 0);
                        return sprintf('%05d-%05d', $options, -$remaining);
                    })
                    ->values();

                if ($nextSubjects->isEmpty()) {
                    break;
                }

                $progress = false;
                foreach ($nextSubjects as $subject) {
                    $subjectId = (int) $subject->id;
                    if (($lectureRemaining[$subjectId] ?? 0) <= 0) {
                        continue;
                    }

                    $ok = $this->tryPlace(
                        $courseId,
                        (int) $year,
                        $semester,
                        $subject,
                        $subjectTeacherCandidates[$subjectId] ?? [],
                        $semesterType,
                        1,
                        new EloquentCollection([$lectureRoomsByYear[(int) $year]]),
                        $state,
                        $rowsToInsert,
                        $teacherAvailabilityByTeacher
                    );

                    if ($ok) {
                        $lectureRemaining[$subjectId]--;
                        $progress = true;
                    }
                }

                if (!$progress) {
                    foreach ($nextSubjects as $subject) {
                        if (($lectureRemaining[(int) $subject->id] ?? 0) > 0) {
                            $errors[] = "Unable to place lecture subject {$subject->name} for Year {$year} (Semester {$semester}).";
                        }
                    }
                    break;
                }
            }
        }

        if (!empty($errors)) {
            throw new RuntimeException("Timetable generation failed:\n- " . implode("\n- ", $errors));
        }

        $this->assertNoFreeSlots($state, $selectedYears, $courseId);

        return ['rows' => $rowsToInsert];
    }

    private function tryPlace(
        int $courseId,
        int $year,
        int $semester,
        Subject $subject,
        array $teacherCandidates,
        string $semesterType,
        int $duration,
        EloquentCollection $candidateRooms,
        array &$state,
        array &$rowsToInsert,
        array $teacherAvailabilityByTeacher
    ): bool {
        if (empty($teacherCandidates)) {
            return false;
        }

        $roomIds = $candidateRooms->pluck('id')->sort()->values();
        $candidates = collect();

        foreach ($this->days() as $day) {
            for ($start = 1; $start <= (max($this->slots()) - $duration + 1); $start++) {
                $candidates->push([
                    'day' => $day,
                    'slots' => range($start, $start + $duration - 1),
                ]);
            }
        }
        // Order candidates deterministically instead of shuffling: prefer earlier days and slots
        $dayOrder = array_flip($this->days());
        $candidates = $candidates->sortBy(function ($candidate) use ($dayOrder) {
            $dayIndex = $dayOrder[$candidate['day']] ?? 999;
            $slotStart = $candidate['slots'][0] ?? 999;
            return sprintf('%03d-%03d', $dayIndex, $slotStart);
        })->values();

        $guard = 0;
        foreach ($candidates as $candidate) {
            $guard++;
            if ($guard > self::SLOT_RETRY_GUARD) {
                break;
            }

            foreach ($roomIds as $roomId) {
                $orderedTeachers = collect($teacherCandidates)
                    ->map(fn($id) => (int) $id)
                    ->unique()
                    ->sortBy(fn(int $teacherId) => (int) ($state['teacher_day_load'][$teacherId][(string) $candidate['day']] ?? 0))
                    ->values();

                foreach ($orderedTeachers as $teacherId) {
                    if (
                        !$this->conflictValidator->canPlaceForGeneration(
                            $courseId,
                            $year,
                            $teacherId,
                            (int) $roomId,
                            (string) $candidate['day'],
                            $candidate['slots'],
                            $semesterType,
                            $state,
                            $teacherAvailabilityByTeacher
                        )
                    ) {
                        continue;
                    }

                    $labBlockId = $duration > 1 ? (string) Str::uuid() : null;
                    $now = now();
                    foreach ($candidate['slots'] as $slot) {
                        $rowsToInsert[] = [
                            'course_id' => $courseId,
                            'year_number' => $year,
                            'semester_number' => $semester,
                            'subject_id' => (int) $subject->id,
                            'teacher_id' => $teacherId,
                            'classroom_id' => (int) $roomId,
                            'day' => (string) $candidate['day'],
                            'slot_number' => (int) $slot,
                            'lab_block_id' => $labBlockId,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];

                        $this->occupy(
                            $state,
                            $teacherId,
                            $courseId,
                            $year,
                            (int) $roomId,
                            (string) $candidate['day'],
                            (int) $slot
                        );
                    }

                    return true;
                }
            }
        }

        return false;
    }

    private function estimateSingleSlotOptions(
        int $courseId,
        int $year,
        int $teacherId,
        int $roomId,
        string $semesterType,
        array $state,
        array $teacherAvailabilityByTeacher,
        array &$cache = []
    ): int {
        $cacheKey = "{$teacherId}-{$roomId}";
        if (isset($cache[$cacheKey])) {
            return $cache[$cacheKey];
        }

        $count = 0;
        foreach ($this->days() as $day) {
            foreach ($this->slots() as $slot) {
                if (
                    $this->conflictValidator->canPlaceForGeneration(
                        $courseId,
                        $year,
                        $teacherId,
                        $roomId,
                        $day,
                        [$slot],
                        $semesterType,
                        $state,
                        $teacherAvailabilityByTeacher
                    )
                ) {
                    $count++;
                }
            }
        }

        return $cache[$cacheKey] = $count;
    }

    private function occupy(
        array &$state,
        int $teacherId,
        int $courseId,
        int $year,
        int $roomId,
        string $day,
        int $slot
    ): void {
        $classKey = $this->classKey($courseId, $year);
        $state['teacher'][$teacherId][$day][$slot] = true;
        $state['class'][$classKey][$day][$slot] = true;
        $state['room'][$roomId][$day][$slot] = true;
        $state['teacher_day_load'][$teacherId][$day] = (int) ($state['teacher_day_load'][$teacherId][$day] ?? 0) + 1;
    }

    private function buildStateFromExisting(Collection $rows): array
    {
        $state = [
            'teacher' => [],
            'class' => [],
            'room' => [],
            'teacher_day_load' => [],
            'teacher_limit' => [],
        ];

        $teacherIds = $rows->pluck('teacher_id')->map(fn($id) => (int) $id)->unique()->values();
        $globalTeacherLimit = $this->accessService->teacherMaxLecturesPerDay();
        $limits = Teacher::query()
            ->whereIn('id', $teacherIds->all())
            ->pluck('max_lectures_per_day', 'id')
            ->map(fn($v) => min(max(1, (int) ($v ?? 6)), $globalTeacherLimit));

        foreach ($rows as $row) {
            $teacherId = (int) $row->teacher_id;
            $state['teacher_limit'][$teacherId] = (int) ($limits->get($teacherId, 6));
            $this->occupy(
                $state,
                $teacherId,
                (int) $row->course_id,
                (int) $row->year_number,
                (int) $row->classroom_id,
                (string) $row->day,
                (int) $row->slot_number
            );
        }

        return $state;
    }

    private function assertNoFreeSlots(array $state, Collection $selectedYears, int $courseId): void
    {
        foreach ($selectedYears as $year) {
            $classKey = $this->classKey($courseId, (int) $year);
            foreach ($this->days() as $day) {
                $occupied = 0;
                foreach ($this->slots() as $slot) {
                    if (($state['class'][$classKey][$day][$slot] ?? false) === true) {
                        $occupied++;
                    }
                }
                if ($occupied !== count($this->slots())) {
                    throw new RuntimeException("Generation validation failed: Year {$year} has free slots on " . ucfirst($day) . '.');
                }
            }
        }
    }

    private function assertWeeklyHourTarget(Collection $classSubjects, int $year, int $semester): void
    {
        if ($classSubjects->isEmpty()) {
            throw new RuntimeException("No subjects found for Year {$year}, Semester {$semester}.");
        }

        $total = $classSubjects->sum(fn(Subject $subject) => $this->hoursPerWeek($subject));
        if ((int) $total !== self::WEEKLY_SLOT_TARGET) {
            throw new RuntimeException(
                "Year {$year}, Semester {$semester} has {$total} total subject hours/week. Required: " . self::WEEKLY_SLOT_TARGET . '.'
            );
        }
    }

    private function assertTeacherCapacityForSelection(
        Collection $filteredSubjects,
        Collection $teacherMap,
        Collection $selectedYears,
        string $semesterType
    ): void {
        $requiredByTeacher = [];
        foreach ($filteredSubjects as $subject) {
            $semester = (int) ($subject->semester_number ?? $subject->semester_sequence ?? 0);
            $year = $this->yearFromSemester($semester);
            if (!$selectedYears->contains($year)) {
                continue;
            }

            $teacherId = (int) ($teacherMap->get($subject->id) ?? 0);
            if ($teacherId <= 0) {
                continue;
            }

            $requiredByTeacher[$teacherId] = (int) ($requiredByTeacher[$teacherId] ?? 0) + $this->hoursPerWeek($subject);
        }

        if (empty($requiredByTeacher)) {
            return;
        }

        $globalTeacherLimit = $this->accessService->teacherMaxLecturesPerDay();
        $teacherRows = Teacher::query()
            ->whereIn('id', array_keys($requiredByTeacher))
            ->get(['id', 'max_lectures_per_day', 'user_id']);

        $errors = [];
        foreach ($teacherRows as $teacher) {
            $teacherId = (int) $teacher->id;
            $required = (int) ($requiredByTeacher[$teacherId] ?? 0);
            $capacity = 0;
            foreach ($this->days() as $day) {
                $existing = $this->repository->teacherDayLoad($teacherId, $day, $semesterType);
                $effectiveLimit = min(max(1, (int) ($teacher->max_lectures_per_day ?? 6)), $globalTeacherLimit);
                $capacity += max(0, $effectiveLimit - $existing);
            }

            if ($required > $capacity) {
                $name = $teacher->user?->name ?? "Teacher {$teacherId}";
                $errors[] = "{$name} requires {$required} slots but has only {$capacity} free slots in {$semesterType} timetable.";
            }
        }

        if (!empty($errors)) {
            throw new RuntimeException("Teacher capacity validation failed:\n- " . implode("\n- ", $errors));
        }
    }

    private function rebalanceTeacherAssignments(
        Collection $subjects,
        Collection $initialTeacherMap,
        Collection $activeTeachers,
        string $semesterType
    ): Collection {
        $activeTeacherIds = $activeTeachers->pluck('id')->map(fn($id) => (int) $id)->values();
        if ($activeTeacherIds->isEmpty()) {
            throw new RuntimeException('No active teachers selected for timetable generation.');
        }

        $assignmentRows = TeacherSubjectAssignment::query()
            ->whereIn('subject_id', $subjects->pluck('id'))
            ->where('is_active', true)
            ->whereIn('teacher_id', $activeTeacherIds->all())
            ->orderByDesc('assigned_date')
            ->orderByDesc('id')
            ->get(['subject_id', 'teacher_id']);

        $candidatesBySubject = $assignmentRows
            ->groupBy('subject_id')
            ->map(fn(Collection $rows) => $rows->pluck('teacher_id')->map(fn($id) => (int) $id)->unique()->values());

        $teacherCapacity = [];
        $globalTeacherLimit = $this->accessService->teacherMaxLecturesPerDay();
        foreach ($activeTeachers as $teacher) {
            $teacherId = (int) $teacher->id;
            $max = min(max(1, (int) ($teacher->max_lectures_per_day ?? 6)), $globalTeacherLimit);
            $free = 0;
            foreach ($this->days() as $day) {
                $free += max(0, $max - $this->repository->teacherDayLoad($teacherId, $day, $semesterType));
            }
            $teacherCapacity[$teacherId] = $free;
        }

        $assigned = collect();
        $orderedSubjects = $subjects
            ->sortBy(function (Subject $subject) use ($candidatesBySubject) {
                $subjectId = (int) $subject->id;
                $candidateCount = $candidatesBySubject->has($subjectId)
                    ? $candidatesBySubject->get($subjectId)->count()
                    : 999;
                return sprintf('%03d-%03d', $candidateCount, -$this->hoursPerWeek($subject));
            })
            ->values();

        foreach ($orderedSubjects as $subject) {
            $subjectId = (int) $subject->id;
            $required = $this->hoursPerWeek($subject);

            $preferred = (int) ($initialTeacherMap->get($subjectId) ?? 0);
            $candidates = $candidatesBySubject->get($subjectId, collect());
            if ($preferred > 0 && !$candidates->contains($preferred) && $activeTeacherIds->contains($preferred)) {
                $candidates = collect([$preferred])->merge($candidates)->unique()->values();
            }
            // Fallback to selected active department teachers when mapped candidates are overloaded.
            $candidates = $candidates->merge($activeTeacherIds)->unique()->values();

            $best = $candidates
                ->sortByDesc(fn(int $teacherId) => (int) ($teacherCapacity[$teacherId] ?? 0))
                ->first();

            if (!$best || (int) ($teacherCapacity[$best] ?? 0) < $required) {
                $names = $activeTeachers
                    ->filter(fn($t) => $candidates->contains((int) $t->id))
                    ->map(fn($t) => ($t->user?->name ?? ("Teacher {$t->id}")) . ' (' . ((int) ($teacherCapacity[(int) $t->id] ?? 0)) . ' free)')
                    ->values()
                    ->all();
                throw new RuntimeException(
                    "Teacher balancing failed for {$subject->name}: required {$required} slots, candidate capacity insufficient" .
                    (!empty($names) ? ' [' . implode(', ', $names) . ']' : '.')
                );
            }

            $assigned->put($subjectId, (int) $best);
            $teacherCapacity[$best] = (int) $teacherCapacity[$best] - $required;
        }

        return $assigned;
    }

    private function assertInitialCoverageFeasibility(
        int $courseId,
        int $year,
        int $semester,
        Collection $classSubjects,
        array $subjectTeacherCandidates,
        string $semesterType,
        int $fixedLectureRoomId,
        array $state
    ): void {
        $hasLabs = $classSubjects->contains(fn(Subject $subject) => $this->isLabSubject($subject));
        if ($hasLabs) {
            return;
        }

        $teacherIds = $classSubjects
            ->flatMap(fn(Subject $subject) => collect($subjectTeacherCandidates[(int) $subject->id] ?? []))
            ->map(fn($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        $classKey = $this->classKey($courseId, $year);
        // iterate over working days/slots rather than hardcoded constants
        foreach ($this->days() as $day) {
            foreach ($this->slots() as $slot) {
                if (($state['class'][$classKey][$day][$slot] ?? false) === true) {
                    continue;
                }
                if (($state['room'][$fixedLectureRoomId][$day][$slot] ?? false) === true) {
                    throw new RuntimeException(
                        "Year {$year}, Semester {$semester} cannot be fully scheduled: fixed lecture room is occupied on " . ucfirst($day) . " slot {$slot}."
                    );
                }

                $teacherFree = $teacherIds->contains(function (int $teacherId) use ($day, $slot, $state) {
                    return !($state['teacher'][$teacherId][$day][$slot] ?? false);
                });

                if (!$teacherFree) {
                    throw new RuntimeException(
                        "Year {$year}, Semester {$semester} cannot be fully scheduled: no mapped teacher is free on " . ucfirst($day) . " slot {$slot}."
                    );
                }
            }
        }
    }

    private function isLabSubject(Subject $subject): bool
    {
        if (!empty($subject->type)) {
            return strtolower((string) $subject->type) === 'lab';
        }

        return (bool) ($subject->is_lab ?? false);
    }

    private function labDuration(Subject $subject): int
    {
        return max(2, min(3, (int) ($subject->lab_duration ?? $subject->lab_block_hours ?? 2)));
    }

    private function hoursPerWeek(Subject $subject): int
    {
        return max(1, (int) ($subject->hours_per_week ?? $subject->weekly_hours ?? $subject->credits ?? 4));
    }

    private function yearFromSemester(int $semesterNumber): int
    {
        return max(1, (int) ceil($semesterNumber / 2));
    }

    private function classKey(int $courseId, int $year): string
    {
        return "{$courseId}-{$year}";
    }

    private function optimizeFixedLectureRoomsForSemester(int $courseId, Collection $years, string $semesterType): void
    {
        $reserved = [];
        foreach ($years as $year) {
            $year = (int) $year;
            $fixed = $this->repository->exactLectureClassroomForCourseYear($courseId, $year);
            if (!$fixed) {
                continue;
            }

            $free = $this->repository->lectureRoomFreeSlotCount((int) $fixed->id, $semesterType);
            if ($free >= self::WEEKLY_SLOT_TARGET) {
                $reserved[] = (int) $fixed->id;
                continue;
            }

            $replacement = $this->repository->bestUnassignedLectureRoom($semesterType, $reserved);
            if (!$replacement) {
                continue;
            }

            $replacementFree = $this->repository->lectureRoomFreeSlotCount((int) $replacement->id, $semesterType);
            if ($replacementFree < self::WEEKLY_SLOT_TARGET) {
                continue;
            }

            $this->repository->assignLectureRoomToCourseYear((int) $replacement->id, $courseId, $year);
            $reserved[] = (int) $replacement->id;
        }
    }

    private function buildSubjectTeacherCandidates(
        Collection $subjects,
        Collection $defaultTeacherMap,
        array $activeTeacherIds
    ): array {
        if (empty($activeTeacherIds)) {
            return [];
        }

        $subjectIds = $subjects->pluck('id')->map(fn($id) => (int) $id)->all();
        $assignmentRows = TeacherSubjectAssignment::query()
            ->whereIn('subject_id', $subjectIds)
            ->where('is_active', true)
            ->whereIn('teacher_id', $activeTeacherIds)
            ->orderByDesc('assigned_date')
            ->orderByDesc('id')
            ->get(['subject_id', 'teacher_id']);

        $map = [];
        foreach ($subjects as $subject) {
            $subjectId = (int) $subject->id;
            $preferred = (int) ($defaultTeacherMap->get($subjectId) ?? 0);
            $assigned = $assignmentRows
                ->where('subject_id', $subjectId)
                ->pluck('teacher_id')
                ->map(fn($id) => (int) $id)
                ->values();

            $candidates = collect();
            if ($preferred > 0 && in_array($preferred, $activeTeacherIds, true)) {
                $candidates->push($preferred);
            }

            // Always allow selected active department teachers as fallback to avoid hard deadlocks.
            $candidates = $candidates->merge($assigned)->merge($activeTeacherIds)->unique()->values();
            $map[$subjectId] = $candidates->all();
        }

        return $map;
    }
}
