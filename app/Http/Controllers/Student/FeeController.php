<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;

class FeeController extends Controller
{
    public function index()
    {
        $student = Student::with(['fees.feeStructure', 'fees.payments'])->where('user_id', auth()->id())->first();
        if (!$student) {
            abort(403, 'Student profile not found.');
        }

        $allFees = $student->fees()->get();

        $fees = $student->fees()
            ->with(['feeStructure', 'payments'])
            ->latest('due_date')
            ->paginate(10);

        $totalAmount = $allFees->sum('total_amount');
        $totalPaid = $allFees->sum('paid_amount');
        $totalPending = $allFees->sum('pending_amount');

        return view('student.fees.index', compact('fees', 'totalAmount', 'totalPaid', 'totalPending'));
    }
}
