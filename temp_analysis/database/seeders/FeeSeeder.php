<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Fee;
use App\Models\Student;

class FeeSeeder extends Seeder
{
    public function run()
    {
        $totalAmount = 75000;

        foreach (Student::all() as $student) {

            // Random payment scenario
            $scenario = rand(1, 3);

            if ($scenario === 1) {
                // Fully paid
                $paidAmount = $totalAmount;
                $status = 'paid';
            } elseif ($scenario === 2) {
                // Partially paid
                $paidAmount = rand(20000, 60000);
                $status = 'partial';
            } else {
                // Not paid
                $paidAmount = 0;
                $status = 'unpaid';
            }

            Fee::firstOrCreate(
                ['student_id' => $student->id],
                [
                    'total_amount' => $totalAmount,
                    'paid_amount' => $paidAmount,
                    'due_amount' => $totalAmount - $paidAmount,
                    'payment_status' => $status,
                ]
            );
        }
    }
}