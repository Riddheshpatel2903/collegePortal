<?php

namespace Database\Factories;

use App\Models\Subject;
use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubjectFactory extends Factory
{
    protected $model = Subject::class;

    public function definition(): array
    {
        $semester = $this->faker->numberBetween(1, 8);
        $code = sprintf('SUB%03d', $this->faker->unique()->numberBetween(1, 999));

        return [
            'name' => "{$code} - " . $this->faker->words(3, true),
            'course_id' => Course::inRandomOrder()->first()?->id ?? Course::factory(),
            'semester_sequence' => $semester,
            'credits' => $this->faker->numberBetween(3, 4),
            'weekly_hours' => $this->faker->numberBetween(3, 5),
            'is_lab' => $this->faker->boolean(25),
            'lab_block_hours' => fn (array $attributes) => !empty($attributes['is_lab']) ? $this->faker->numberBetween(2, 3) : null,
        ];
    }
}
