<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

class CourseFactory extends Factory
{
    protected $model = Course::class;

    public function definition(): array
    {
        return [
            'department_id' => Department::query()->inRandomOrder()->value('id') ?? Department::factory(),
            'name' => 'B.E. ' . $this->faker->words(2, true),
            'duration_years' => 4,
            'semesters_per_year' => 2,
            'is_active' => true,
        ];
    }
}

