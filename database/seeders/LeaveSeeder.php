<?php

namespace Database\Seeders;

use App\Models\Leave;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LeaveSeeder extends Seeder
{
    public function run(): void
    {
        $students = Student::query()->inRandomOrder()->limit(30)->get();
        $teachers = Teacher::query()->inRandomOrder()->limit(10)->get();
        $hodApproverIds = User::query()->where('role', 'hod')->pluck('id')->all();
        $adminId = User::query()->where('role', 'admin')->value('id');

        DB::transaction(function () use ($students, $teachers, $hodApproverIds, $adminId) {
            foreach ($students as $student) {
                $this->seedLeave($student, 'student', $hodApproverIds, $adminId);
            }
            foreach ($teachers as $teacher) {
                $this->seedLeave($teacher, 'teacher', $hodApproverIds, $adminId);
            }
        });
    }

    private function seedLeave(object $leaveable, string $role, array $hodApproverIds, ?int $adminId): void
    {
        $start = now()->subDays(random_int(1, 45));
        $end = (clone $start)->addDays(random_int(1, 4));
        $statusRoll = random_int(1, 100);
        $status = $statusRoll <= 40 ? 'pending' : ($statusRoll <= 75 ? 'approved' : 'rejected');

        $approvedBy = null;
        $approvedAt = null;
        $remarks = null;
        $stage = 'hod_review';

        if ($status !== 'pending') {
            $stage = 'closed';
            $approvedBy = !empty($hodApproverIds) ? $hodApproverIds[array_rand($hodApproverIds)] : $adminId;
            $approvedAt = now()->subDays(random_int(0, 20));
            $remarks = $status === 'approved' ? 'Approved after review.' : 'Rejected due to academic schedule.';
        }

        Leave::query()->create([
            'leaveable_type' => $leaveable::class,
            'leaveable_id' => $leaveable->id,
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'leave_type' => fake()->randomElement(['sick', 'casual', 'emergency']),
            'requested_by_role' => $role,
            'reason' => fake()->sentence(14),
            'status' => $status,
            'current_stage' => $stage,
            'approved_by' => $approvedBy,
            'approval_remarks' => $remarks,
            'approved_at' => $approvedAt,
            'applied_at' => $start->copy()->subDays(random_int(1, 5)),
        ]);
    }
}

