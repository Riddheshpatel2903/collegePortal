<?php

namespace Database\Factories;

use App\Models\Assignment;
use App\Models\Teacher;
use App\Models\Subject;
use App\Models\Course;
use App\Models\Semester;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssignmentFactory extends Factory
{
    protected $model = Assignment::class;

    public function definition(): array
    {
        $dueDate = $this->faker->dateTimeBetween('now', '+1 month');
        return [
            'teacher_id' => Teacher::inRandomOrder()->first()?->id ?? Teacher::factory(),
            'subject_id' => Subject::inRandomOrder()->first()?->id ?? Subject::factory(),
            'course_id' => Course::inRandomOrder()->first()?->id ?? Course::factory(),
            'semester_id' => Semester::inRandomOrder()->first()?->id ?? Semester::factory(),
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(3),
            'total_marks' => $this->faker->randomElement([50, 100]),
            'due_date' => $dueDate,
            'attachment_path' => null,
            'status' => $this->faker->randomElement(['draft', 'published']),
            'allow_late_submission' => $this->faker->boolean(70),
            'late_until' => $this->faker->boolean(70) ? $this->faker->dateTimeBetween($dueDate, '+1 week') : null,
            'is_active' => true,
        ];
    }
}
