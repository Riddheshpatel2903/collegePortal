<?php

namespace Database\Factories;

use App\Models\Classroom;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClassroomFactory extends Factory
{
    protected $model = Classroom::class;

    public function definition(): array
    {
        $block = $this->faker->randomElement(['A', 'B', 'C']);
        $floor = $this->faker->numberBetween(1, 4);
        $room = $this->faker->numberBetween(1, 25);

        return [
            'name' => "{$block}-{$floor}{$room}",
            'capacity' => $this->faker->randomElement([60, 70, 80, 90]),
        ];
    }
}
