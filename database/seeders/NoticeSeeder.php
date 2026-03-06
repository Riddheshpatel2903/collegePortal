<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Notice;
use App\Models\User;
use Illuminate\Database\Seeder;

class NoticeSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();
        $departments = Department::all();

        $global = [
            ['Mid-Semester Examination Schedule', 'Mid-semester exams start from next Monday.', 'all'],
            ['Annual Tech Fest Registration', 'Register for Tech Fest by end of this week.', 'student'],
            ['Academic Council Meeting', 'All HODs and senior faculty to attend at 11:00 AM.', 'hod'],
        ];

        foreach ($global as [$title, $content, $target]) {
            Notice::updateOrCreate(
                ['title' => $title],
                [
                    'content' => $content,
                    'posted_by' => $admin?->id,
                    'target_role' => $target,
                    'notice_for' => 'all',
                    'priority' => fake()->randomElement(['medium', 'high']),
                    'is_active' => true,
                    'expiry_date' => now()->addDays(rand(7, 45)),
                ]
            );
        }

        foreach ($departments as $department) {
            Notice::updateOrCreate(
                [
                    'title' => $department->name . ' Department Review',
                    'department_id' => $department->id,
                ],
                [
                    'content' => 'Internal review and audit meeting for ' . $department->name . '.',
                    'posted_by' => $department->hod_id ?? $admin?->id,
                    'target_role' => 'teacher',
                    'notice_for' => 'teachers',
                    'priority' => 'medium',
                    'is_active' => true,
                    'expiry_date' => now()->addDays(rand(10, 30)),
                ]
            );
        }
    }
}
