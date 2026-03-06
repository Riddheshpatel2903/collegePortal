<?php

namespace Database\Seeders;

use App\Models\FeeStructure;
use App\Models\Payment;
use App\Models\Student;
use App\Models\StudentFee;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StudentFeeSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = \App\Models\User::query()->where('role', 'admin')->value('id');
        $students = Student::query()->with('course')->get();
        if ($students->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($students, $adminId) {
            foreach ($students as $student) {
                $structure = FeeStructure::query()
                    ->where('course_id', $student->course_id)
                    ->where('year_number', $student->current_year)
                    ->where('fee_type', 'tuition')
                    ->first();

                if (!$structure) {
                    continue;
                }

                $bucket = random_int(1, 100);
                $status = $bucket <= 70 ? 'paid' : ($bucket <= 90 ? 'pending' : 'overdue');
                $dueDate = $status === 'overdue'
                    ? now()->subDays(random_int(10, 120))->toDateString()
                    : now()->addDays(random_int(10, 120))->toDateString();

                $paidAmount = $status === 'paid' ? (float) $structure->amount : 0.0;

                $studentFee = StudentFee::query()->updateOrCreate(
                    [
                        'student_id' => $student->id,
                        'academic_year' => $student->current_year,
                        'fee_structure_id' => $structure->id,
                    ],
                    [
                        'semester_id' => $student->current_semester_id,
                        'total_amount' => $structure->amount,
                        'paid_amount' => $paidAmount,
                        'status' => $status,
                        'due_date' => $dueDate,
                        'last_payment_date' => $status === 'paid' ? now()->subDays(random_int(1, 40))->toDateString() : null,
                    ]
                );

                if ($status === 'paid') {
                    Payment::query()->updateOrCreate(
                        ['student_fee_id' => $studentFee->id],
                        [
                            'student_id' => $student->id,
                            'amount' => $studentFee->total_amount,
                            'payment_date' => $studentFee->last_payment_date ?? now()->toDateString(),
                            'payment_mode' => 'cash',
                            'receipt_number' => 'RCT-' . now()->format('Ymd') . '-' . str_pad((string) $student->id, 6, '0', STR_PAD_LEFT),
                            'remarks' => 'Offline payment received at college counter',
                            'collected_by' => $adminId,
                        ]
                    );
                }
            }
        });
    }
}

