<?php

namespace Database\Factories;

use App\Models\Student;
use App\Models\User;
use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        $course = Course::query()->inRandomOrder()->first();
        $courseId = $course?->id ?? Course::factory();
        $year = $this->faker->numberBetween(1, 4);

        return [
            'user_id' => User::factory()->create([
                'role' => 'student',
                'password' => Hash::make('password'),
            ])->id,
            'department_id' => $course?->department_id,
            'course_id' => $courseId,
            'current_year' => $year,
            'roll_number' => 'STU' . $this->faker->unique()->numberBetween(100000, 999999),
            'gtu_enrollment_no' => '21' . $this->faker->unique()->numerify('01201######'),
            'registration_number' => 'REG' . $this->faker->unique()->numerify('######'),
            'admission_date' => $this->faker->date('Y-m-d', '-1 year'),
            'admission_year' => $this->faker->numberBetween(2022, 2025),
            'phone' => $this->faker->phoneNumber(),
            'address' => $this->faker->address(),
            'academic_status' => 'active',
            'student_status' => 'active',
            'is_active' => true,
        ];
    }
}
