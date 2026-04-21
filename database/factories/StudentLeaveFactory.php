<?php

namespace Database\Factories;

use App\Models\Student;
use App\Models\StudentLeave;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentLeaveFactory extends Factory
{
    protected $model = StudentLeave::class;

    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('-1 month', '+1 month');
        $endDate = (clone $startDate)->modify('+'.$this->faker->numberBetween(1, 5).' days');
        $status = $this->faker->randomElement(['pending', 'approved', 'rejected']);

        return [
            'student_id' => Student::inRandomOrder()->first()?->id ?? Student::factory(),
            'approved_by' => $status !== 'pending' ? (Teacher::inRandomOrder()->first()?->id ?? Teacher::factory()) : null,
            'leave_type' => $this->faker->randomElement(['Sick Leave', 'Personal', 'Emergency', 'Event']),
            'reason' => $this->faker->sentence(),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_days' => $startDate->diff($endDate)->days + 1,
            'status' => $status,
            'faculty_remark' => $status !== 'pending' ? $this->faker->sentence() : null,
            'applied_at' => $this->faker->dateTimeBetween('-2 months', $startDate),
            'reviewed_at' => $status !== 'pending' ? $this->faker->dateTimeBetween($startDate, 'now') : null,
        ];
    }
}
