<?php

namespace Database\Factories;

use App\Models\AssignmentSubmission;
use App\Models\Assignment;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssignmentSubmissionFactory extends Factory
{
    protected $model = AssignmentSubmission::class;

    public function definition(): array
    {
        $assignment = Assignment::inRandomOrder()->first() ?? Assignment::factory()->create();
        $submittedAt = $this->faker->dateTimeBetween($assignment->created_at, $assignment->due_date->addDays(2));
        $isLate = $submittedAt > $assignment->due_date;
        $status = $isLate ? 'late' : 'submitted';

        // 50% chance of being graded
        $isGraded = $this->faker->boolean(50);
        if ($isGraded) {
            $status = 'graded';
            $marks = $this->faker->numberBetween(40, $assignment->total_marks);
            $feedback = $this->faker->sentence();
        } else {
            $marks = null;
            $feedback = null;
        }

        return [
            'assignment_id' => $assignment->id,
            'student_id' => Student::inRandomOrder()->first()?->id ?? Student::factory(),
            'file_path' => 'assignments/submissions/sample.pdf',
            'submitted_at' => $submittedAt,
            'marks_obtained' => $marks,
            'feedback' => $feedback,
            'status' => $status,
        ];
    }
}
