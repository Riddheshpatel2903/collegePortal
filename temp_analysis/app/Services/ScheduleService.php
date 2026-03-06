<?php

namespace App\Services;

use App\Models\Schedule;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ScheduleService
{
    public function create(array $payload): Schedule
    {
        $this->assertNoConflicts($payload);

        return DB::transaction(fn () => Schedule::create($payload));
    }

    public function update(Schedule $schedule, array $payload): Schedule
    {
        $this->assertNoConflicts($payload, $schedule->id);

        return DB::transaction(function () use ($schedule, $payload) {
            $schedule->update($payload);
            return $schedule->refresh();
        });
    }

    public function assertNoConflicts(array $payload, ?int $ignoreId = null): void
    {
        $this->assertFixedSlotRule($payload);

        if ($this->hasTeacherConflict($payload, $ignoreId)) {
            throw ValidationException::withMessages([
                'teacher_id' => 'Teacher has a timetable conflict for this slot.',
            ]);
        }

        if ($this->hasRoomConflict($payload, $ignoreId)) {
            throw ValidationException::withMessages([
                'classroom_id' => 'Classroom is already booked for this slot.',
            ]);
        }

        if ($this->hasClassConflict($payload, $ignoreId)) {
            throw ValidationException::withMessages([
                'subject_id' => 'The same subject already has a slot at this time.',
            ]);
        }
    }

    private function hasTeacherConflict(array $payload, ?int $ignoreId): bool
    {
        return Schedule::query()
            ->where('teacher_id', $payload['teacher_id'])
            ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
            ->where(fn ($query) => $this->applyOverlap($query, $payload))
            ->exists();
    }

    private function hasRoomConflict(array $payload, ?int $ignoreId): bool
    {
        return Schedule::query()
            ->where('classroom_id', $payload['classroom_id'])
            ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
            ->where(fn ($query) => $this->applyOverlap($query, $payload))
            ->exists();
    }

    private function hasClassConflict(array $payload, ?int $ignoreId): bool
    {
        return Schedule::query()
            ->where('semester_id', $payload['semester_id'] ?? null)
            ->where('subject_id', $payload['subject_id'])
            ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
            ->where(fn ($query) => $this->applyOverlap($query, $payload))
            ->exists();
    }

    private function applyOverlap($query, array $payload): void
    {
        $query->where('day_of_week', $payload['day_of_week'])
            ->where('start_time', '<', $payload['end_time'])
            ->where('end_time', '>', $payload['start_time']);
    }

    private function assertFixedSlotRule(array $payload): void
    {
        $start = substr((string) $payload['start_time'], 0, 5);
        $end = substr((string) $payload['end_time'], 0, 5);
        $allowedBlocks = config('timetable.slot_blocks', []);

        $isAllowed = collect($allowedBlocks)->contains(function ($block) use ($start, $end) {
            return isset($block[0], $block[1]) && $block[0] === $start && $block[1] === $end;
        });

        if (!$isAllowed) {
            throw ValidationException::withMessages([
                'start_time' => 'Slot must match one fixed 1-hour timetable block.',
            ]);
        }
    }
}
