<?php

namespace Database\Factories;

use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceRecordFactory extends Factory
{
    protected $model = AttendanceRecord::class;

    public function definition(): array
    {
        return [
            'attendance_session_id' => AttendanceSession::inRandomOrder()->first()?->id ?? AttendanceSession::factory(),
            'student_id' => Student::inRandomOrder()->first()?->id ?? Student::factory(),
            'status' => $this->faker->boolean(85) ? 'present' : 'absent',
        ];
    }
}
