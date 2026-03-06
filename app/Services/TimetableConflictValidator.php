<?php

namespace App\Services;

use App\Repositories\TimetableRepository;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class TimetableConflictValidator
{
    public function __construct(private TimetableRepository $repository)
    {
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
        Collection $teacherAvailabilityByTeacher
    ): bool {
        $classKey = $this->classKey($courseId, $year);

        foreach ($slots as $slot) {
            if (($state['teacher'][$teacherId][$day][$slot] ?? false) === true) {
                return false;
            }
            if (($state['class'][$classKey][$day][$slot] ?? false) === true) {
                return false;
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

        if ($this->repository->hasTeacherConflict($teacherId, $day, $slots, $semesterType)) {
            return false;
        }
        if ($this->repository->hasClassConflict($courseId, $year, $day, $slots, $semesterType)) {
            return false;
        }
        if ($this->repository->hasRoomConflict($roomId, $day, $slots, $semesterType)) {
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
        Collection $teacherAvailabilityByTeacher
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
        Collection $teacherAvailabilityByTeacher
    ): bool {
        $availability = $teacherAvailabilityByTeacher->get($teacherId, collect())
            ->where('day_of_week', $day)
            ->values();

        if ($availability->isEmpty()) {
            return true;
        }

        foreach ($slots as $slotNumber) {
            [$slotStart, $slotEnd] = $this->slotRange((int) $slotNumber);
            $allowed = $availability->contains(function ($row) use ($slotStart, $slotEnd) {
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
        $blocks = config('timetable.slot_blocks', []);
        $idx = max(0, min(count($blocks) - 1, $slotNumber - 1));
        $block = $blocks[$idx] ?? ['09:00', '10:00'];
        return [$block[0], $block[1]];
    }

    private function classKey(int $courseId, int $year): string
    {
        return "{$courseId}-{$year}";
    }
}

