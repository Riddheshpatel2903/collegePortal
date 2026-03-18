<?php

namespace App\Console\Commands\Timetable;

use App\Models\Schedule;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class CheckTimetableConflictsCommand extends Command
{
    protected $signature = 'timetable:check-conflicts
                            {--course= : Filter by course ID}
                            {--day= : Filter by day (monday, tuesday, etc.)}';

    protected $description = 'Scan the generated timetable for conflicts (double-booked teachers, rooms, or classes).';

    public function handle(): int
    {
        $this->info('🔍 Scanning timetable for conflicts...');

        $query = Schedule::query()
            ->with(['subject.course', 'teacher.user', 'classroom', 'semester'])
            ->when($this->option('course'), fn($q) => $q->whereHas('subject', fn($sq) => $sq->where('course_id', (int) $this->option('course'))))
            ->when($this->option('day'), fn($q) => $q->where('day_of_week', strtolower($this->option('day'))));

        $schedules = $query->get();

        if ($schedules->isEmpty()) {
            $this->warn('No timetable entries found. Generate a timetable first.');
            return self::FAILURE;
        }

        $conflicts = [];

        // Group by day + time slot
        $grouped = $schedules->groupBy(fn($s) => $s->day_of_week . '_' . $s->start_time . '_' . $s->end_time);

        foreach ($grouped as $slotKey => $entries) {
            if ($entries->count() < 2) continue;

            // Teacher conflicts
            $teacherGroups = $entries->groupBy('teacher_id');
            foreach ($teacherGroups as $teacherId => $teacherSlots) {
                if ($teacherSlots->count() > 1) {
                    $conflicts[] = [
                        'Type'    => '⚔️  Teacher',
                        'Slot'    => $slotKey,
                        'Details' => $teacherSlots->first()->teacher?->user?->name ?? "Teacher #{$teacherId}",
                        'Count'   => $teacherSlots->count() . ' overlapping',
                    ];
                }
            }

            // Room conflicts
            $roomGroups = $entries->groupBy('classroom_id');
            foreach ($roomGroups as $roomId => $roomSlots) {
                if ($roomSlots->count() > 1) {
                    $conflicts[] = [
                        'Type'    => '🏫 Room',
                        'Slot'    => $slotKey,
                        'Details' => $roomSlots->first()->classroom?->name ?? "Room #{$roomId}",
                        'Count'   => $roomSlots->count() . ' overlapping',
                    ];
                }
            }
        }

        $this->newLine();

        if (empty($conflicts)) {
            $this->info("✅ No conflicts found in {$schedules->count()} timetable entries.");
            return self::SUCCESS;
        }

        $this->error("Found " . count($conflicts) . " conflict(s):");
        $this->table(['Type', 'Time Slot', 'Details', 'Count'], $conflicts);
        $this->newLine();
        $this->warn('Fix these conflicts from the HOD Timetable Workspace or Admin Timetable panel.');

        return self::FAILURE;
    }
}
