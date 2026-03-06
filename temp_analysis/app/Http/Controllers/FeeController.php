<?php
// app/Http/Controllers/FeeController.php

namespace App\Http\Controllers;

use App\Models\StudentFee;
use App\Models\Student;
use App\Models\Payment;
use App\Services\FeeService;
use Illuminate\Http\Request;

class FeeController extends Controller
{
    protected $feeService;

    public function __construct(FeeService $feeService)
    {
        $this->feeService = $feeService;
    }

    /**
     * Display student fees
     */
    public function index(Request $request)
    {
        $query = StudentFee::with(['student', 'semester', 'feeStructure']);

        // Filters
        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('semester_id')) {
            $query->where('semester_id', $request->semester_id);
        }

        $fees = $query->latest()->paginate(15);

        return view('fees.index', compact('fees'));
    }

    /**
     * Show fee payment form
     */
    public function pay(StudentFee $fee)
    {
        abort(403, 'Online fee payment is currently disabled. Please pay at the accounts office.');
    }

    /**
     * Process payment
     */
    public function processPayment(Request $request, StudentFee $fee)
    {
        abort(403, 'Online fee payment is currently disabled. Please pay at the accounts office.');
    }

    /**
     * Show payment receipt
     */
    public function receipt(Payment $payment)
    {
        $payment->load(['studentFee.feeStructure', 'student', 'collector']);

        return view('fees.receipt', compact('payment'));
    }

    /**
     * Show student fee dashboard
     */
    public function studentFees()
    {
        $student = auth()->user()->student;

        if (!$student) {
            abort(403, 'Student profile not found.');
        }

        $fees = $student->fees()->with(['semester', 'feeStructure', 'payments'])->get();

        $totalPending = $fees->where('status', '!=', 'paid')->sum('pending_amount');
        $totalPaid = $fees->sum('paid_amount');

        return view('fees.student-fees', compact('fees', 'totalPending', 'totalPaid'));
    }
}
