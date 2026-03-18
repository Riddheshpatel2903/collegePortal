<?php

namespace App\Services;

use App\Models\Student;
use App\Models\FeeStructure;
use App\Models\StudentFee;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FeeService
{
    public function applyYearFeeToStudent(Student $student, int $yearNumber)
    {
        $feeStructures = FeeStructure::where('course_id', $student->course_id)
            ->where('year_number', $yearNumber)
            ->where('is_active', true)
            ->get();

        $appliedFees = [];

        foreach ($feeStructures as $feeStructure) {
            // Check if fee already applied
            $existingFee = StudentFee::where('student_id', $student->id)
                ->where('academic_year', $yearNumber)
                ->where('fee_structure_id', $feeStructure->id)
                ->first();

            if (!$existingFee) {
                $studentFee = StudentFee::create([
                    'student_id' => $student->id,
                    'academic_year' => $yearNumber,
                    'fee_structure_id' => $feeStructure->id,
                    'total_amount' => $feeStructure->amount,
                    'paid_amount' => 0,
                    'status' => 'pending',
                    'due_date' => Carbon::createFromDate(now()->year, 7, 31),
                ]);

                $appliedFees[] = $studentFee;
            }
        }

        return $appliedFees;
    }

    public function applyFeesToAllStudentsInYear(int $courseId, int $yearNumber)
    {
        $students = Student::where('course_id', $courseId)
            ->where('current_year', $yearNumber)
            ->where('student_status', 'active')
            ->get();

        $count = 0;

        foreach ($students as $student) {
            $this->applyYearFeeToStudent($student, $yearNumber);
            $count++;
        }

        return $count;
    }

    /**
     * Record a payment
     */
    public function recordPayment(StudentFee $studentFee, array $paymentData)
    {
        return DB::transaction(function () use ($studentFee, $paymentData) {
            $payment = $studentFee->payments()->create([
                'student_id' => $studentFee->student_id,
                'receipt_number' => $paymentData['receipt_number'] ?? null,
                'amount' => $paymentData['amount'],
                'payment_date' => $paymentData['payment_date'] ?? now(),
                'payment_mode' => $paymentData['payment_mode'],
                'transaction_id' => $paymentData['transaction_id'] ?? null,
                'remarks' => $paymentData['remarks'] ?? null,
                'collected_by' => $paymentData['collected_by'] ?? \Illuminate\Support\Facades\Auth::id()
            ]);

            // Update student fee
            $studentFee->paid_amount += $paymentData['amount'];
            $studentFee->last_payment_date = $payment->payment_date;
            $studentFee->updatePaymentStatus();

            return $payment;
        });
    }

    /**
     * Mark overdue fees
     */
    public function markOverdueFees()
    {
        return StudentFee::where('status', '!=', 'paid')
            ->where('due_date', '<', now())
            ->update(['status' => 'overdue']);
    }
}
