<?php

namespace Database\Seeders;

use App\Models\AssignmentSubmission;
use App\Models\Assignment;
use App\Models\Student;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SubmissionSeeder extends Seeder
{
    public function run(): void
    {
        $assignments = Assignment::where('status', 'published')->get();

        foreach ($assignments as $assignment) {
            $academicYear = $assignment->academic_year ?: 1;
            $students = Student::where('course_id', $assignment->course_id)
                ->where('current_year', $academicYear)
                ->get();

            if ($students->isEmpty())
                continue;

            $submissionRate = rand(60, 95) / 100;
            $count = (int) ($students->count() * $submissionRate);
            if ($count === 0)
                continue;

            $studentsToSubmit = $students->random($count);
            $submissions = [];

            foreach ($studentsToSubmit as $student) {
                $dueDate = Carbon::parse($assignment->due_date);
                $submittedAt = (clone $dueDate)->addHours(rand(-72, 24));
                $isLate = $submittedAt->gt($dueDate);
                $status = $isLate ? 'late' : 'submitted';

                $marks = null;
                $feedback = null;
                if (rand(1, 10) > 4) {
                    $status = 'graded';
                    $marks = rand(40, $assignment->total_marks);
                    $feedback = fake()->sentence();
                }

                $submissions[] = [
                    'assignment_id' => $assignment->id,
                    'student_id' => $student->id,
                    'file_path' => 'assignments/submissions/sample.pdf',
                    'submitted_at' => $submittedAt,
                    'marks_obtained' => $marks,
                    'feedback' => $feedback,
                    'status' => $status,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                if (count($submissions) >= 100) {
                    $this->batchUpsert($submissions);
                    $submissions = [];
                }
            }

            if (!empty($submissions)) {
                $this->batchUpsert($submissions);
            }
        }
    }

    private function batchUpsert(array $data): void
    {
        AssignmentSubmission::upsert(
            $data,
            ['assignment_id', 'student_id'],
            ['file_path', 'submitted_at', 'marks_obtained', 'feedback', 'status', 'updated_at']
        );
    }
}
