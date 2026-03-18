<?php

namespace App\Services\Timetable;

use App\Repositories\TimetableRepository;
use App\Services\PortalAccessService;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class TimetableConflictValidator
{
    private static array $slotRangeCache = [];

    public function __construct(
        private TimetableRepository $repository,
        private PortalAccessService $accessService
    ) {
    }

    public function canPlaceForGeneration(
        int $courseId,
        int $year,
        int $teacherId,
        int $roomId,
        string $day,
        array $slots,
        string $semesterType,
        array $state,
        array $teacherAvailabilityByTeacher
    ): bool {
        $classKey = $this->classKey($courseId, $year);

        // Fetch subject to check section
        $subject = \App\Models\Subject::find($state['subject_id'] ?? 0);
        $subjectSection = $subject ? $this->extractSection($subject->name) : null;

        foreach ($slots as $slot) {
            if (($state['teacher'][$teacherId][$day][$slot] ?? false) === true) {
                return false;
            }
            
            $classData = $state['class'][$classKey][$day][$slot] ?? [];
            if ($subjectSection === null) {
                // COMMON subject
                if (!empty($classData)) return false;
            } else {
                // Section-specific subject
                if (($classData[$subjectSection] ?? false) || ($classData['COMMON'] ?? false)) {
                    return false;
                }
            }

            if (($state['room'][$roomId][$day][$slot] ?? false) === true) {
                return false;
            }
        }

        $maxPerDay = max(1, (int) ($state['teacher_limit'][$teacherId] ?? 6));
        $dayLoad = (int) ($state['teacher_day_load'][$teacherId][$day] ?? 0);
        if (($dayLoad + count($slots)) > $maxPerDay) {
            return false;
        }

        if (!$this->teacherAvailableForSlots($teacherId, $day, $slots, $teacherAvailabilityByTeacher)) {
            return false;
        }

        return true;
    }

    public function assertNoConflictsForEdit(
        int $courseId,
        int $yearNumber,
        int $teacherId,
        int $classroomId,
        string $day,
        array $slots,
        string $semesterType,
        array $ignoreIds,
        int $teacherMaxPerDay,
        array $teacherAvailabilityByTeacher
    ): void {
        if ($this->repository->hasTeacherConflict($teacherId, $day, $slots, $semesterType, $ignoreIds)) {
            throw ValidationException::withMessages(['teacher_id' => 'Teacher is already assigned in the selected slot.']);
        }
        if ($this->repository->hasClassConflict($courseId, $yearNumber, $day, $slots, $semesterType, $ignoreIds)) {
            throw ValidationException::withMessages(['slot_number' => 'Class already has a lecture in the selected slot.']);
        }
        if ($this->repository->hasRoomConflict($classroomId, $day, $slots, $semesterType, $ignoreIds)) {
            throw ValidationException::withMessages(['classroom_id' => 'Classroom is already occupied in the selected slot.']);
        }

        if (!$this->teacherAvailableForSlots($teacherId, $day, $slots, $teacherAvailabilityByTeacher)) {
            throw ValidationException::withMessages(['teacher_id' => 'Teacher is unavailable for one or more selected slot(s).']);
        }

        $dayLoad = $this->repository->teacherDayLoad($teacherId, $day, $semesterType, $ignoreIds);
        if (($dayLoad + count($slots)) > max(1, $teacherMaxPerDay)) {
            throw ValidationException::withMessages(['teacher_id' => "Teacher daily limit ({$teacherMaxPerDay}) exceeded."]);
        }
    }

    private function teacherAvailableForSlots(
        int $teacherId,
        string $day,
        array $slots,
        array $teacherAvailabilityByTeacher
    ): bool {
        // Handle pre-processed structure: [teacher_id][day] => Collection|array
        $availability = $teacherAvailabilityByTeacher[$teacherId][$day] ?? collect();

        if ($availability instanceof Collection) {
            if ($availability->isEmpty())
                return true;
        } elseif (empty($availability)) {
            return true;
        }

        foreach ($slots as $slotNumber) {
            [$slotStart, $slotEnd] = $this->slotRange((int) $slotNumber);

            // availability here is expected to be a Collection of availability records for the specific teacher AND day.
            $allowed = $availability->contains(function ($row) use ($slotStart, $slotEnd) {
                // Remove seconds if present for comparison
                $start = substr((string) $row->start_time, 0, 5);
                $end = substr((string) $row->end_time, 0, 5);
                return $start <= $slotStart && $end >= $slotEnd;
            });

            if (!$allowed) {
                return false;
            }
        }

        return true;
    }

    private function slotRange(int $slotNumber): array
    {
        if (isset(self::$slotRangeCache[$slotNumber])) {
            return self::$slotRangeCache[$slotNumber];
        }

        $timeSlots = $this->accessService->timeSlots();
        $idx = $slotNumber - 1;
        $slotString = $timeSlots->get($idx);

        if (!$slotString) {
            return self::$slotRangeCache[$slotNumber] = ['09:00', '10:00'];
        }

        [$start, $end] = explode('-', $slotString);

        return self::$slotRangeCache[$slotNumber] = [$start, $end];
    }

    private function extractSection(string $name): ?string
    {
        if (preg_match('/[ \-]([A-Z])(?:\s|$)/i', $name, $matches)) {
            return strtoupper($matches[1]);
        }
        return null;
    }

    private function classKey(int $courseId, int $year): string
    {
        return "{$courseId}-{$year}";
    }
}

