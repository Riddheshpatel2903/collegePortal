<?php

namespace Database\Seeders;

use App\Models\AcademicPhase;
use App\Models\Classroom;
use App\Models\Course;
use App\Models\Schedule;
use App\Models\Semester;
use App\Models\TeacherSubjectAssignment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TimetableSeeder extends Seeder
{
    private const DAYS = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];

    private const SLOT_BLOCKS = [
        ['09:00:00', '10:00:00'],
        ['10:00:00', '11:00:00'],
        ['11:00:00', '12:00:00'],
        ['12:00:00', '13:00:00'],
        ['14:00:00', '15:00:00'],
        ['15:00:00', '16:00:00'],
    ];

    public function run(): void
    {
        if (Classroom::query()->count() < 20) {
            for ($i = 1; $i <= 20; $i++) {
                Classroom::query()->firstOrCreate(
                    ['name' => 'CR-'.str_pad((string) $i, 3, '0', STR_PAD_LEFT)],
                    ['capacity' => random_int(60, 90)]
                );
            }
        }

        $phase = AcademicPhase::query()->where('is_active', true)->first();
        $phaseIndex = strcasecmp((string) ($phase?->phase_name ?? 'Odd'), 'Even') === 0 ? 2 : 1;
        $courses = Course::query()->get();
        $roomIds = Classroom::query()->pluck('id')->all();

        DB::transaction(function () use ($courses, $phaseIndex, $roomIds) {
            Schedule::query()->delete();

            $teacherBusy = [];
            $roomBusy = [];
            $classBusy = [];

            $days = config('timetable.working_days', []);
            $slots = config('timetable.slot_blocks', []);

            foreach ($courses as $course) {
                for ($year = 1; $year <= 4; $year++) {
                    $semesterNo = (($year - 1) * 2) + $phaseIndex;
                    $semester = Semester::query()
                        ->where('course_id', $course->id)
                        ->where('semester_number', $semesterNo)
                        ->first();

                    $assignments = TeacherSubjectAssignment::query()
                        ->with('subject')
                        ->whereHas('subject', function ($query) use ($course, $semesterNo) {
                            $query->where('course_id', $course->id)
                                ->where('semester_sequence', $semesterNo);
                        })
                        ->get();

                    foreach ($assignments as $assignment) {
                        $subject = $assignment->subject;
                        if (! $subject || ! $assignment->teacher_id) {
                            continue;
                        }

                        $needed = max(1, (int) ($subject->weekly_hours ?? 4));
                        $classKey = "{$course->id}-{$semesterNo}";
                        $scheduled = 0;

                        foreach ($days as $day) {
                            foreach ($slots as $slotIdx => $slot) {
                                if ($scheduled >= $needed) {
                                    break 2;
                                }

                                $teacherKey = "{$assignment->teacher_id}-{$day}-{$slotIdx}";
                                $classSlotKey = "{$classKey}-{$day}-{$slotIdx}";
                                if (($teacherBusy[$teacherKey] ?? false) || ($classBusy[$classSlotKey] ?? false)) {
                                    continue;
                                }

                                $roomId = null;
                                foreach ($roomIds as $rid) {
                                    $roomKey = "{$rid}-{$day}-{$slotIdx}";
                                    if (! ($roomBusy[$roomKey] ?? false)) {
                                        $roomId = $rid;
                                        $roomBusy[$roomKey] = true;
                                        break;
                                    }
                                }

                                if (! $roomId) {
                                    continue;
                                }

                                Schedule::query()->create([
                                    'semester_id' => $semester?->id,
                                    'subject_id' => $subject->id,
                                    'teacher_id' => $assignment->teacher_id,
                                    'classroom_id' => $roomId,
                                    'day_of_week' => $day,
                                    'start_time' => $slot[0],
                                    'end_time' => $slot[1],
                                ]);

                                $teacherBusy[$teacherKey] = true;
                                $classBusy[$classSlotKey] = true;
                                $scheduled++;
                            }
                        }
                    }
                }
            }
        });
    }
}
