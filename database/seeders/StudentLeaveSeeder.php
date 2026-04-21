<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\StudentLeave;
use App\Models\Teacher;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class StudentLeaveSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $students = Student::all();
        $teachers = Teacher::all();

        if ($students->isEmpty() || $teachers->isEmpty()) {
            return;
        }

        $leaveTypes = ['Sick Leave', 'Personal Leave', 'Emergency Leave', 'Event Leave'];
        $statuses = ['pending', 'approved', 'rejected'];

        foreach ($students as $student) {
            // Create 2-3 leave requests for each student
            for ($i = 0; $i < rand(1, 3); $i++) {
                $status = $statuses[array_rand($statuses)];
                $startDate = Carbon::now()->subDays(rand(1, 30))->addDays(rand(0, 60));
                $endDate = (clone $startDate)->addDays(rand(1, 5));
                $totalDays = $startDate->diffInDays($endDate) + 1;

                $approvedBy = null;
                $reviewedAt = null;
                $facultyRemark = null;

                if ($status !== 'pending') {
                    $approvedBy = $teachers->random()->id;
                    $reviewedAt = Carbon::now()->subDays(rand(0, 5));
                    $facultyRemark = $status === 'approved' ? 'Approved, catch up on your work.' : 'Request denied due to academic schedule.';
                }

                StudentLeave::create([
                    'student_id' => $student->id,
                    'approved_by' => $approvedBy,
                    'leave_type' => $leaveTypes[array_rand($leaveTypes)],
                    'reason' => 'I need leave for '.rand(1, 5).' days due to '.($i % 2 == 0 ? 'health issues.' : 'personal family commitments.'),
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'total_days' => $totalDays,
                    'status' => $status,
                    'faculty_remark' => $facultyRemark,
                    'applied_at' => (clone $startDate)->subDays(rand(2, 5)),
                    'reviewed_at' => $reviewedAt,
                ]);
            }
        }
    }
}
