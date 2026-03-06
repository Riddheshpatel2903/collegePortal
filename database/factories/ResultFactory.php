<?php

namespace Database\Factories;

use App\Models\Result;
use App\Models\Student;
use App\Models\Semester;
use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

class ResultFactory extends Factory
{
    protected $model = Result::class;

    public function definition(): array
    {
        $semesterNumber = $this->faker->numberBetween(1, 8);
        $sgpa = $this->faker->randomFloat(2, 4.5, 9.5);
        $cgpa = $this->faker->randomFloat(2, 4.5, 9.5);

        return [
            'student_id' => Student::inRandomOrder()->first()?->id ?? Student::factory(),
            'semester_id' => Semester::inRandomOrder()->first()?->id ?? Semester::factory(),
            'course_id' => Course::inRandomOrder()->first()?->id ?? Course::factory(),
            'academic_year' => (int) ceil($semesterNumber / 2),
            'semester_number' => $semesterNumber,
            'sgpa' => $sgpa,
            'cgpa' => $cgpa,
            'total_credits_earned' => $this->faker->numberBetween(12, 30),
            'backlog_subjects' => 0,
            'result_status' => 'pass',
            'promoted' => true,
            'result_declared_date' => $this->faker->dateTimeBetween('-6 months', 'now'),
        ];
    }
}
