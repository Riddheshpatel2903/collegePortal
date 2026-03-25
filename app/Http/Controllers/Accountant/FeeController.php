<?php

namespace App\Http\Controllers\Accountant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\StudentFee;
use App\Services\FeeService;

class FeeController extends Controller
{
    public function __construct(private FeeService $feeService)
    {
    }

    protected function ensureAllStudentsHaveFeeEntries(): void
    {
        $studentsWithoutFees = Student::active()
            ->whereDoesntHave('fees')
            ->get();

        /** @var Student $student */
        foreach ($studentsWithoutFees as $student) {
            if (!($student instanceof Student)) {
                continue;
            }

            $this->feeService->applyYearFeeToStudent($student, (int) ($student->current_year ?: 1));
        }
    }

    public function index(Request $request)
    {
        // Core accountant fee dashboard: always show fee records.
        $query = StudentFee::with(['student.user', 'student.course', 'student.semester'])
            ->orderBy('due_date', 'asc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('roll_number', 'like', "%{$search}%")
                    ->orWhere('gtu_enrollment_no', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($u) use ($search) {
                        $u->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $fees = $query->paginate(25)->withQueryString();

        $summary = [
            'total' => StudentFee::sum('total_amount'),
            'collected' => StudentFee::sum('paid_amount'),
            'pending' => StudentFee::where('status', '!=', 'paid')->sum('pending_amount'),
            'unpaid' => StudentFee::where('status', 'pending')->sum('total_amount'),
        ];

        return view('accountant.fees.index', compact('fees', 'summary'));
    }

    public function update(Request $request, StudentFee $fee)
    {
        $request->validate([
            'payment_amount' => 'required|numeric|min:0.1|max:' . ($fee->total_amount - $fee->paid_amount),
            'receipt_number' => 'nullable|string|max:50|unique:payments,receipt_number',
            'remarks' => 'nullable|string|max:255',
        ]);

        $paymentAmount = (float) $request->input('payment_amount');

        $payment = $this->feeService->recordPayment($fee, [
            'amount' => $paymentAmount,
            'payment_mode' => $request->input('payment_mode', 'cash'),
            'remarks' => $request->input('remarks'),
            'collected_by' => \Illuminate\Support\Facades\Auth::id(),
        ]);

        if ($request->filled('receipt_number')) {
            $payment->update(['receipt_number' => $request->input('receipt_number')]);
        }

        return redirect()->route('accountant.fees.index')
            ->with('success', 'Payment recorded for ' . ($fee->student->user->name ?? 'student') . '.');
    }

    public function assign(Student $student)
    {
        $yearNumber = (int) $student->current_year ?: 1;
        $applied = $this->feeService->applyYearFeeToStudent($student, $yearNumber);

        if (count($applied) === 0) {
            return redirect()->route('accountant.fees.index')
                ->with('warning', 'No fee structure found for this student. Configure fee structure and try again.');
        }

        return redirect()->route('accountant.fees.index')
            ->with('success', 'Fee allocation created for student successfully.');
    }

    public function history(Request $request)
    {
        $payments = \App\Models\Payment::with(['studentFee.student.user'])
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('accountant.fees.history', compact('payments'));
    }
}
