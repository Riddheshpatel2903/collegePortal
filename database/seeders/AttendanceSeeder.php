<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\Student;
use App\Models\TeacherSubjectAssignment;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $assignments = TeacherSubjectAssignment::with('subject.course')
            ->where('is_active', true)
            ->get();

        foreach ($assignments as $assignment) {
            $subject = $assignment->subject;
            $course = $subject?->course;
            if (! $subject || ! $course) {
                continue;
            }

            $semesterNumber = (int) $subject->semester_sequence;
            $academicYear = (int) ceil($semesterNumber / max(1, (int) $course->semesters_per_year));

            $students = Student::select('id')
                ->where('course_id', $course->id)
                ->where('current_year', $academicYear)
                ->get();

            if ($students->isEmpty()) {
                continue;
            }

            $sessionCount = rand(4, 7);
            for ($i = 0; $i < $sessionCount; $i++) {
                $sessionDate = Carbon::now()->subDays($i * 3 + rand(0, 2));

                $session = AttendanceSession::create([
                    'semester_subject_id' => null,
                    'teacher_id' => $assignment->teacher_id,
                    'course_id' => $course->id,
                    'subject_id' => $subject->id,
                    'academic_year' => $academicYear,
                    'semester_number' => $semesterNumber,
                    'date' => $sessionDate->toDateString(),
                    'start_time' => '09:00:00',
                    'end_time' => '10:00:00',
                    'session_type' => rand(1, 10) > 8 ? 'practical' : 'lecture',
                    'topic' => 'Session topic '.rand(1, 500),
                    'is_completed' => true,
                ]);

                $rows = [];
                foreach ($students as $student) {
                    $rows[] = [
                        'attendance_session_id' => $session->id,
                        'student_id' => $student->id,
                        'status' => rand(1, 100) <= 84 ? 'present' : 'absent',
                        'remarks' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                Attendance::insert($rows);
            }
        }
    }
}
