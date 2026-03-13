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
    /**
     * Working days returns the currently configured weekday sequence.
     */
    private function days(?int $courseId = null, ?string $semesterType = null): array
    {
        return $this->accessService->workingDays();
    }

    /**
     * Numeric slot indexes respecting the configured slots-per-day.
     */
    private function slots(?int $courseId = null, ?string $semesterType = null): array
    {
        $count = $this->accessService->slotsPerDay($courseId, $semesterType);
        return range(1, $count);
    }
    private const WEEKLY_SLOT_TARGET = 30;
    private const SLOT_RETRY_GUARD = 500;
    private const FULL_GENERATION_RETRIES = 100;

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
            'lecture_rooms_pool' => $this->repository->lectureClassroomsPool($course->id),
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
            ->map(fn($y) => (int) $y)
            ->filter(fn(int $y) => $allYears->contains($y))
            ->unique()->values();

        if ($selectedYears->isEmpty()) {
            throw ValidationException::withMessages(['selected_years' => 'At least one class year must be selected.']);
        }

        $selectedTeacherIds   = collect($payload['selected_teacher_ids'] ?? [])
            ->map(fn($id) => (int) $id)->filter()->unique()->values();
        $selectedClassroomIds = collect($payload['selected_classroom_ids'] ?? [])
            ->map(fn($id) => (int) $id)->filter()->unique()->values();

        $semesterType = strtolower((string) ($payload['semester_type'] ?? 'odd'));

        // Ensure rooms exist
        $this->repository->ensureFixedLectureClassrooms($course->id, $selectedYears->all());
        $this->repository->ensureLabClassrooms($course->id, 2);

        // Load subjects
        $semesterNumbers = $this->repository->semesterNumbersByType($course, $semesterType)->all();
        $allSubjects     = $this->repository->courseSubjectsForSemesters($course->id, $semesterNumbers);

        if ($allSubjects->isEmpty()) {
            throw new RuntimeException('No subjects found for the selected course and semester type.');
        }

        // Teacher map: subject_id → teacher_id (preferred / assigned)
        $teacherMap = $this->repository->teacherMapForSubjects($allSubjects->pluck('id'));

        // Active teachers (respect optional filter)
        $activeTeachers = $this->repository->departmentTeachers(
            (int) $course->department_id,
            $selectedTeacherIds->isEmpty() ? null : $selectedTeacherIds->all()
        );
        $activeTeacherIds = $activeTeachers->pluck('id')->map(fn($id) => (int) $id)->all();

        // Candidate teachers per subject (preferred first, then all active as fallback)
        $subjectTeacherCandidates = $this->buildSubjectTeacherCandidates(
            $allSubjects, $teacherMap, $activeTeacherIds
        );

        // Filter subjects to selected years
        $filteredSubjects = $allSubjects->filter(function (Subject $s) use ($selectedYears) {
            $year = $this->yearFromSemester((int) ($s->semester_number ?? $s->semester_sequence ?? 1));
            return $selectedYears->contains($year);
        })->filter(fn(Subject $s) => $s->totalWeeklySlots() > 0)->values();

        if ($filteredSubjects->isEmpty()) {
            throw new RuntimeException('No schedulable subjects left after year filters or zero-hour overrides.');
        }

        // Lecture room per year
        $lectureRoomsByYear = [];
        foreach ($selectedYears as $year) {
            $room = $this->repository->exactLectureClassroomForCourseYear($course->id, (int) $year);
            if (!$room) {
                throw new RuntimeException("No fixed lecture classroom found for Year {$year}. Please ensure classrooms are configured.");
            }
            $lectureRoomsByYear[(int) $year] = $room;
        }

        // Lab and Lecture room pools
        $labRooms = $this->repository->labClassrooms($course->id);
        if ($selectedClassroomIds->isNotEmpty()) {
            $labRooms = $labRooms->filter(fn($r) => $selectedClassroomIds->contains((int) $r->id))->values();
        }
        $lectureRoomsPool = $this->repository->lectureClassroomsPool($course->id);

        // Teacher availability
        $teacherAvailability = $this->repository
            ->teacherAvailabilities($activeTeacherIds)
            ->groupBy('teacher_id')
            ->map(fn($rows) => $rows->groupBy('day_of_week'));

        // Build baseline occupancy from OTHER courses only.
        // This prevents pre-occupying slots that belong to the course being regenerated.
        $otherRows     = $this->repository->otherTimetableBySemesterType($semesterType, $course->id);
        $baselineState = $this->buildStateFromExisting($otherRows);

        // Pre-occupy Holidays
        $this->preOccupyHolidays($baselineState);

        // Run up to FULL_GENERATION_RETRIES attempts with shuffled day/slot ordering
        $rows = $this->retryEngine->run(self::FULL_GENERATION_RETRIES, function (int $attempt) use (
            $selectedYears, $semesterType, $filteredSubjects, $subjectTeacherCandidates,
            $course, $labRooms, $lectureRoomsByYear, $baselineState, $teacherAvailability,
            $lectureRoomsPool
        ) {
            return $this->runGeneration(
                $selectedYears,
                $semesterType,
                $filteredSubjects,
                $subjectTeacherCandidates,
                $course->id,
                $labRooms,
                $lectureRoomsPool,
                $lectureRoomsByYear,
                $baselineState,
                $teacherAvailability->toArray(),
                $attempt
            );
        });

        // Persist: clear old entries then bulk insert new ones
        return DB::transaction(function () use ($rows, $course, $semesterType, $selectedYears) {
            $this->repository->clearCourseSemesterTypeYears($course->id, $semesterType, $selectedYears->all());
            $this->repository->bulkInsert($rows);
            return [
                'generated_count' => count($rows),
                'course_id'       => $course->id,
                'semester_type'   => $semesterType,
                'selected_years'  => $selectedYears->all(),
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
        $days = $this->days($courseId, $semesterType);
        $slots = $this->slots($courseId, $semesterType);

        foreach ($years as $year) {
            foreach ($days as $day) {
                foreach ($slots as $slot) {
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
            'course'            => $course,
            'semester_type'     => strtolower($semesterType),
            'days'              => $this->days(),
            'slots'             => $this->slots(),
            'time_slots'        => $this->accessService->timeSlots(),
            'years'             => $years,
            'grid'              => $grid,
            'teachers'          => $this->repository->departmentTeachers((int) $course->department_id),
            'subjects_by_year'  => $subjectMap,
            'classrooms_by_year'=> $classroomMap,
            'rows_count'        => $rows->count(),
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
            $isLabBlock   = !empty($row->lab_block_id);
            $blockRows    = $isLabBlock
                ? $this->repository->rowsByLabBlock((string) $row->lab_block_id)
                : new EloquentCollection([$row]);

            $duration  = max(1, $blockRows->count());
            $day       = (string) $payload['day'];
            $startSlot = (int) $payload['slot_number'];
            $slots     = range($startSlot, $startSlot + $duration - 1);

            if (max($slots) > max($this->slots())) {
                throw ValidationException::withMessages(['slot_number' => 'Selected slot exceeds available day slots.']);
            }

            $subject = \App\Models\Subject::query()->findOrFail((int) $payload['subject_id']);
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

            $teacherId   = (int) $payload['teacher_id'];
            $classroomId = (int) $payload['classroom_id'];
            $ignoreIds   = $blockRows->pluck('id')->map(fn($id) => (int) $id)->all();

            if ($subjectIsLab) {
                $classroom = \App\Models\Classroom::query()->findOrFail($classroomId);
                if ($classroom->type !== 'lab') {
                    throw ValidationException::withMessages(['classroom_id' => 'Lab subject must use a lab classroom.']);
                }
            } else {
                $classroom = \App\Models\Classroom::query()->findOrFail($classroomId);
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

            $teacher    = Teacher::query()->findOrFail($teacherId);
            $teacherMax = min(
                max(1, (int) ($teacher->max_lectures_per_day ?? 6)),
                $this->accessService->teacherMaxLecturesPerDay()
            );
            $teacherAvailabilityByTeacher = $this->repository->teacherAvailabilities([$teacherId])
                ->groupBy('teacher_id')
                ->map(fn($rows) => $rows->groupBy('day_of_week'))
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
                $slotRow->subject_id   = (int) $payload['subject_id'];
                $slotRow->teacher_id   = $teacherId;
                $slotRow->classroom_id = $classroomId;
                $slotRow->day          = $day;
                $slotRow->slot_number  = $slots[$index];
            }

            $this->repository->saveMany($blockRows);
        });
    }


    /**
     * Core generation engine.
     *
     * Strategy:
     * 1. Place all LAB subjects first (they need consecutive slots + specific room type).
     * 2. Fill remaining slots with LECTURE subjects using a "most-hours-remaining" priority.
     * 3. Days / slots are shuffled on each retry attempt to escape local optima.
     *
     * The occupancy state tracks teacher, class-year, and room occupation.
     * No slot is ever double-booked because every placement checks the state before committing.
     */
    private function runGeneration(
        Collection $selectedYears,
        string $semesterType,
        Collection $filteredSubjects,
        array $subjectTeacherCandidates,
        int $courseId,
        EloquentCollection $labRooms,
        EloquentCollection $lectureRoomsPool,
        array $lectureRoomsByYear,
        array $baselineState,
        array $teacherAvailability,
        int $attempt = 1
    ): array {
        $days       = $this->days($courseId, $semesterType);
        $slotCount  = count($this->slots($courseId, $semesterType));
        $state      = $baselineState;
        $rows       = [];
        $errors     = [];

        // 1. Prepare All Subjects Pool
        $allLabs = [];
        $allLectures = [];
        foreach ($selectedYears as $year) {
            $year = (int)$year;
            $semester = $this->repository->semesterForYearByType($year, $semesterType);
            $classKey = $this->classKey($courseId, $year);
            
            $yearSubjects = $filteredSubjects->filter(
                fn(Subject $s) => (int)($s->semester_number ?? $s->semester_sequence ?? 0) === $semester
            );

            foreach ($yearSubjects as $subject) {
                $subjectId = (int)$subject->id;
                $commonData = [
                    'subject' => $subject,
                    'year' => $year,
                    'semester' => $semester,
                    'classKey' => $classKey,
                    'section' => $this->extractSection($subject->name),
                    'primaryRoomId' => (int)($lectureRoomsByYear[$year]->id ?? 0),
                    'candidates' => $subjectTeacherCandidates[$subjectId] ?? [],
                ];

                $practicalHours = (int) ($subject->practical_hours ?: 0);
                // Fallback for legacy data if LTP isn't used
                if ($practicalHours === 0 && ($subject->is_lab || str_contains(strtolower($subject->type ?? ''), 'lab'))) {
                    $practicalHours = (int) ($subject->weekly_hours ?: $subject->credits ?: 4);
                }

                if ($practicalHours > 0) {
                    $labItem = $commonData;
                    $labItem['hours'] = $practicalHours;
                    $allLabs[] = $labItem;
                }

                $lectureHours = (int) ($subject->lecture_hours ?: 0) + (int) ($subject->tutorial_hours ?: 0);
                // Fallback for legacy data: if not explicitly marked as lab and LTP is missing
                if ($lectureHours === 0 && $practicalHours === 0) {
                    $lectureHours = (int) ($subject->weekly_hours ?: $subject->credits ?: 4);
                }

                if ($lectureHours > 0) {
                    $lectureItem = $commonData;
                    $lectureItem['hours'] = $lectureHours;
                    $allLectures[] = $lectureItem;
                }
            }
        }

        // Strictly sequential slots and preferred Monday-first days for saturation
        $shuffledSlots = range(1, $slotCount);
        $orderedDays = $days; // Guaranteed [monday, tuesday, wednesday, thursday, friday]
        if ($attempt > 40) {
            shuffle($orderedDays);
        }

        // 2. Global Labs Placement (Most Hours First across all years)
        $labPool = collect($allLabs)->sortByDesc('hours')->values();
        foreach ($labPool as $item) {
            $subject = $item['subject'];
            $duration = $this->labDuration($subject);
            $sessions = max(1, (int) floor($item['hours'] / max(1, $duration)));
            
            for ($sess = 0; $sess < $sessions; $sess++) {
                $placed = false;
                foreach ($orderedDays as $day) {
                    for ($start = 1; $start <= ($slotCount - $duration + 1); $start++) {
                        $slots = range($start, $start + $duration - 1);
                        if (!$this->isClassFree($state, $item['classKey'], $day, $slots, $item['section'])) continue;

                        if (!$this->checkGapConstraint($state, $item['classKey'], $day, $slots)) continue;
                        
                        foreach ($labRooms as $labRoom) {
                            $roomId = (int)$labRoom->id;
                            if (!$this->isRoomFree($state, $roomId, $day, $slots)) continue;

                            foreach ($item['candidates'] as $teacherId) {
                                if (!$this->isTeacherFree($state, $teacherId, $day, $slots, $teacherAvailability)) continue;
                                
                                $this->placeSlots($rows, $state, $courseId, $item['year'], $item['semester'], (int)$subject->id, $teacherId, $roomId, $day, $slots, true, $item['section']);
                                $placed = true;
                                break 3;
                            }
                        }
                        if ($placed) break;
                    }
                    if ($placed) break;
                }
                if (!$placed) {
                    $errors[] = "Cannot place lab session " . ($sess + 1) . " of {$subject->name} (Year {$item['year']}, Sem {$item['semester']}).";
                }
            }
        }

        // 3. Global Lectures Placement (Subject-Centric, Highest Pressure First)
        $lecturePool = collect($allLectures)->sortByDesc('hours')->values()->all();
        foreach ($lecturePool as &$item) {
            $remaining = $item['hours'];
            $subject = $item['subject'];
            $subjectId = (int)$subject->id;

            while ($remaining > 0) {
                $placedHour = false;
                $localDays = $orderedDays;
                $localSlots = $shuffledSlots; // Always sequential range
                if ($attempt % 2 === 0) {
                    shuffle($localDays);
                    $item['candidates'] = collect($item['candidates'])->shuffle()->all();
                }

                foreach ($localDays as $day) {
                    foreach ($localSlots as $sl) {
                        if (!$this->isClassFree($state, $item['classKey'], $day, [$sl], $item['section'])) continue;
                        if (!$this->checkGapConstraint($state, $item['classKey'], $day, [$sl])) continue;

                        // Room fallback logic
                        $roomsToTry = collect([$item['primaryRoomId']])
                            ->merge($lectureRoomsPool->pluck('id'))
                            ->unique()
                            ->filter()
                            ->all();
                        
                        foreach ($roomsToTry as $roomId) {
                            $roomId = (int)$roomId;
                            if (!$this->isRoomFree($state, $roomId, $day, [$sl])) continue;

                            foreach ($item['candidates'] as $teacherId) {
                                if (!$this->isTeacherFree($state, $teacherId, $day, [$sl], $teacherAvailability)) continue;

                                $this->placeSlots($rows, $state, $courseId, $item['year'], $item['semester'], $subjectId, $teacherId, $roomId, $day, [$sl], false, $item['section']);
                                $remaining--;
                                $placedHour = true;
                                break 3;
                            }
                        }
                    }
                    if ($placedHour) break; 
                }
                if (!$placedHour) break; 
            }
            if ($remaining > 0) {
                $errors[] = "Cannot place {$remaining} lecture slot(s) for {$subject->name} (Year {$item['year']}, Sem {$item['semester']}).";
            }
        }

        if (!empty($errors)) {
            throw new RuntimeException(implode(' | ', $errors));
        }

        // 4. Validate Daily Density (Min slots if day is active)
        $this->validateDailyDensity($state, $selectedYears, $courseId, $attempt);

        return $rows;
    }

    /**
     * Check whether the teacher has explicit availability records for these slots.
     * Returns true if no availability records exist (open schedule).
     */
    private function teacherFreeInAvailability(int $teacherId, string $day, array $slots, array $availability): bool
    {
        $dayRecords = $availability[$teacherId][$day] ?? null;

        // No records at all → teacher has no restrictions
        if (empty($dayRecords)) {
            return true;
        }

        $timeSlots = $this->accessService->timeSlots();

        foreach ($slots as $slotNumber) {
            $idx = $slotNumber - 1;
            $slotString = $timeSlots->get($idx);
            if (!$slotString) continue;

            [$slotStart, $slotEnd] = explode('-', $slotString);

            $records = $dayRecords instanceof Collection ? $dayRecords : collect($dayRecords);
            $allowed = $records->contains(function ($row) use ($slotStart, $slotEnd) {
                $start = substr((string) $row->start_time, 0, 5);
                $end   = substr((string) $row->end_time, 0, 5);
                return $start <= $slotStart && $end >= $slotEnd;
            });

            if (!$allowed) return false;
        }

        return true;
    }

    private function occupy(
        array &$state,
        int $teacherId,
        int $courseId,
        int $year,
        int $roomId,
        string $day,
        int $slot,
        ?string $section = null
    ): void {
        $classKey = $this->classKey($courseId, $year);
        $state['teacher'][$teacherId][$day][$slot] = true;

        if ($section === null) {
            // "COMMON" subjects occupy all sections
            $state['class'][$classKey][$day][$slot]['COMMON'] = true;
        } else {
            // Specific section subject
            $state['class'][$classKey][$day][$slot][$section] = true;
        }

        $state['room'][$roomId][$day][$slot] = true;
        $state['teacher_day_load'][$teacherId][$day] = (int) ($state['teacher_day_load'][$teacherId][$day] ?? 0) + 1;
        $state['class_day_slots'][$classKey][$day][] = $slot;
    }

    private function isClassFree(array &$state, string $classKey, string $day, array $slots, ?string $section): bool
    {
        foreach ($slots as $sl) {
            if ($section === null) {
                if (!empty($state['class'][$classKey][$day][$sl] ?? [])) return false;
            } else {
                if (($state['class'][$classKey][$day][$sl][$section] ?? false) ||
                    ($state['class'][$classKey][$day][$sl]['COMMON'] ?? false)) {
                    return false;
                }
            }
        }
        return true;
    }

    private function isRoomFree(array &$state, int $roomId, string $day, array $slots): bool
    {
        if ($roomId <= 0) return true;
        foreach ($slots as $sl) {
            if ($state['room'][$roomId][$day][$sl] ?? false) return false;
        }
        return true;
    }

    private function isTeacherFree(array &$state, int $teacherId, string $day, array $slots, array $availability): bool
    {
        foreach ($slots as $sl) {
            if ($state['teacher'][$teacherId][$day][$sl] ?? false) return false;
        }

        if (!$this->teacherFreeInAvailability($teacherId, $day, $slots, $availability)) return false;

        $limit   = (int)($state['teacher_limit'][$teacherId] ?? 6);
        $dayLoad = (int)($state['teacher_day_load'][$teacherId][$day] ?? 0);
        if ($dayLoad + count($slots) > $limit) return false;

        return true;
    }

    private function placeSlots(array &$rows, array &$state, int $courseId, int $year, int $semester, int $subjectId, int $teacherId, int $roomId, string $day, array $slots, bool $isLab, ?string $section): void
    {
        $now = now();
        $labBlockId = $isLab ? (string)\Illuminate\Support\Str::uuid() : null;
        foreach ($slots as $sl) {
            $rows[] = [
                'course_id'       => $courseId,
                'year_number'     => $year,
                'semester_number' => $semester,
                'subject_id'      => $subjectId,
                'teacher_id'      => $teacherId,
                'classroom_id'    => $roomId,
                'day'             => $day,
                'slot_number'     => $sl,
                'lab_block_id'    => $labBlockId,
                'created_at'      => $now,
                'updated_at'      => $now,
            ];
            $this->occupy($state, $teacherId, $courseId, $year, $roomId, $day, $sl, $section);
        }
    }

    private function buildStateFromExisting(Collection $rows): array
    {
        $state = [
            'teacher' => [],
            'class' => [],
            'room' => [],
            'teacher_day_load' => [],
            'teacher_limit' => [],
            'class_day_slots' => [], // classKey -> day -> [slotIndices]
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

            $section = null;
            if ($row->subject && $row->subject->name) {
                $section = $this->extractSection($row->subject->name);
            }

            $this->occupy(
                $state,
                $teacherId,
                (int) $row->course_id,
                (int) $row->year_number,
                (int) $row->classroom_id,
                (string) $row->day,
                (int) $row->slot_number,
                $section
            );
        }

        return $state;
    }

    /**
     * Soft-check: logs a warning if any class has unfilled slots but does NOT throw,
     * so partial schedules are still persisted rather than discarded entirely.
     */
    private function warnFreeSlots(array $state, Collection $selectedYears, int $courseId): void
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
                $total = count($this->slots());
                if ($occupied < $total) {
                    \Illuminate\Support\Facades\Log::warning(
                        "[Timetable] Year {$year} has " . ($total - $occupied) . " free slot(s) on " . ucfirst($day)
                    );
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
        // Allow any total between 1 and configured slots; only fail if badly configured.
        $maxSlots = count($this->days()) * count($this->slots());
        if ((int) $total < 1 || (int) $total > $maxSlots) {
            throw new RuntimeException(
                "Year {$year}, Semester {$semester} has {$total} total subject hours/week which exceeds the available {$maxSlots} weekly slots."
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
                // For initial check, we don't have a specific subject yet,
                // but we know if ANY common subject exists for this class
                if (($state['class'][$classKey][$day][$slot]['COMMON'] ?? false) === true) {
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

    /**
     * Identify section from subject name (e.g. "Theory-A" -> "A")
     */
    private function extractSection(string $name): ?string
    {
        if (preg_match('/[ \-]([A-Z])(?:\s|$)/i', $name, $matches)) {
            return strtoupper($matches[1]);
        }
        return null;
    }

    private function isLabSubject(Subject $subject): bool
    {
        if ($subject->practical_hours > 0) {
            return true;
        }

        if (!empty($subject->type)) {
            $type = strtolower((string) $subject->type);
            return $type === 'practical' || $type === 'lab';
        }

        return (bool) ($subject->is_lab ?? false);
    }

    private function labDuration(Subject $subject): int
    {
        // If practical_hours is 4, assume 2-hour sessions = 2 sessions? 
        // No, generator currently assumes 1 session of 'duration' length.
        // For GTU, P=2 usually means 1 lab session of 2 hours. P=4 usually means 2 lab sessions.
        // We'll stick to 2 as default session duration for now.
        return max(2, min(3, (int) ($subject->lab_duration ?? $subject->lab_block_hours ?? 2)));
    }

    private function hoursPerWeek(Subject $subject): int
    {
        return $subject->totalWeeklySlots();
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

    private function preOccupyHolidays(array &$state): void
    {
        $holidays = \App\Models\Holiday::all();
        $daysToBlock = [];

        foreach ($holidays as $holiday) {
            $date = \Carbon\Carbon::parse($holiday->date);
            if ($holiday->is_recurring) {
                // Check weekday for recurring holiday in current year
                $date = \Carbon\Carbon::create((int)date('Y'), (int)$date->format('m'), (int)$date->format('d'));
            }
            
            // Only care if it's in the near future or relevant window
            // For weekly timetable, if it's a holiday, we block that weekday.
            $daysToBlock[] = strtolower($date->format('l'));
        }

        $daysToBlock = array_unique($daysToBlock);
        $slots = $this->slots();

        foreach ($daysToBlock as $day) {
            // Block all slots for this day for all classes and rooms
            foreach ($state['class'] as $classKey => &$d) {
                foreach ($slots as $slot) {
                    $state['class'][$classKey][$day][$slot] = true;
                }
            }
            foreach ($state['room'] as $roomId => &$d) {
                foreach ($slots as $slot) {
                    $state['room'][$roomId][$day][$slot] = true;
                }
            }
        }
    }

    private function checkGapConstraint(array $state, string $classKey, string $day, array $newSlots): bool
    {
        $existing = $state['class_day_slots'][$classKey][$day] ?? [];
        $combined = array_unique(array_merge($existing, $newSlots));
        if (count($combined) <= 1) return true;

        $min = min($combined);
        $max = max($combined);
        $totalSpan = $max - $min + 1;
        $gaps = $totalSpan - count($combined);

        return $gaps == 0;
    }

    private function validateDailyDensity(array $state, Collection $selectedYears, int $courseId, int $attempt = 1): void
    {
        $days = $this->days();
        $numDays = count($days);

        foreach ($selectedYears as $year) {
            $classKey = $this->classKey($courseId, (int)$year);
            $yearTotalHours = 0;
            $dayCounts = [];

            foreach ($days as $day) {
                $slots = array_unique($state['class_day_slots'][$classKey][$day] ?? []);
                $count = count($slots);
                $yearTotalHours += $count;
                $dayCounts[$day] = $count;
            }

            if ($yearTotalHours === 0) continue;

            // Absolute Uniform (Rectangular) Density:
            // Calculate the "Low" and "High" target for a perfectly balanced week.
            // For 26 hours in 5 days, target is [5, 6].
            $avg = $yearTotalHours / $numDays;
            $lowTarget = (int) floor($avg);
            $highTarget = (int) ceil($avg);

            // Adaptive relaxation only for desperate attempts
            if ($attempt > 90) {
                $lowTarget = 1;
            } elseif ($attempt > 70) {
                $lowTarget = max(1, $lowTarget - 1);
            }

            foreach ($dayCounts as $day => $count) {
                // Every day MUST fall into the [low, high] range or be nearly full.
                // This eliminates "Hanging" days with only 1-2 hours.
                if ($count < $lowTarget) {
                    throw new RuntimeException("Year {$year} is unbalanced on " . ucfirst($day) . ": only {$count} hours, but uniform rule requires at least {$lowTarget} (Attempt {$attempt}).");
                }
            }
            
            // Ensure EVERY working day is used to maximize distribution
            if (count(array_filter($dayCounts)) < $numDays && $lowTarget > 1 && $attempt < 80) {
                 throw new RuntimeException("Year {$year} is skipped some days. We need a uniform rectangular distribution across all {$numDays} days (Attempt {$attempt}).");
            }
        }
    }
}
