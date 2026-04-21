<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class TeacherFactory extends Factory
{
    protected $model = Teacher::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->create([
                'role' => 'teacher',
                'password' => Hash::make('password'),
            ])->id,
            'department_id' => Department::inRandomOrder()->first()?->id ?? Department::factory(),
            'qualification' => $this->faker->randomElement(['M.Tech', 'PhD', 'M.Sc', 'MCA', 'MBA']),
            'phone' => $this->faker->phoneNumber(),
        ];
    }
}
