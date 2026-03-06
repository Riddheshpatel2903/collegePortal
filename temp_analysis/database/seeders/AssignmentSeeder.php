<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\Subject;
use App\Models\TeacherSubjectAssignment;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AssignmentSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = Subject::with('course')->get();
        $teacherBySubject = TeacherSubjectAssignment::where('is_active', true)
            ->pluck('teacher_id', 'subject_id');

        foreach ($subjects as $subject) {
            $teacherId = $teacherBySubject->get($subject->id);
            if (!$teacherId) {
                continue;
            }

            $count = rand(3, 5);
            $semestersPerYear = max(1, (int) ($subject->course?->semesters_per_year ?? 2));
            $semesterNumber = (int) $subject->semester_sequence;
            $academicYear = (int) ceil($semesterNumber / $semestersPerYear);

            for ($i = 1; $i <= $count; $i++) {
                $dueDate = Carbon::now()->addDays(rand(-15, 30));
                Assignment::create([
                    'teacher_id' => $teacherId,
                    'subject_id' => $subject->id,
                    'course_id' => $subject->course_id,
                    'academic_year' => $academicYear,
                    'semester_number' => $semesterNumber,
                    'semester_id' => null,
                    'title' => "Assignment $i for $subject->name",
                    'description' => fake()->paragraph(),
                    'total_marks' => rand(1, 2) * 50,
                    'due_date' => $dueDate,
                    'status' => rand(1, 10) > 2 ? 'published' : 'draft',
                    'allow_late_submission' => rand(1, 10) > 3,
                    'late_until' => rand(1, 10) > 5 ? (clone $dueDate)->addDays(rand(1, 7)) : null,
                    'is_active' => true,
                ]);
            }
        }
    }
}

