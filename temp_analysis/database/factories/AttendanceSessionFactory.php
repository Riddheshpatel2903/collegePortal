<?php

namespace Database\Factories;

use App\Models\AttendanceSession;
use App\Models\Subject;
use App\Models\Semester;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceSessionFactory extends Factory
{
    protected $model = AttendanceSession::class;

    public function definition(): array
    {
        $subject = Subject::inRandomOrder()->first() ?? Subject::factory()->create();
        return [
            'subject_id' => $subject->id,
            'semester_id' => $subject->semester_id,
            'teacher_id' => $subject->teacher->user->id,
            'session_date' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
