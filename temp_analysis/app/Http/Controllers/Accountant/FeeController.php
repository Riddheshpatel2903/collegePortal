<?php

namespace App\Http\Controllers\Accountant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StudentFee;
use App\Services\FeeService;

class FeeController extends Controller
{
    public function __construct(private FeeService $feeService)
    {
    }

    public function index(Request $request)
    {
        $query = StudentFee::with(['student.user', 'student.course', 'student.semester']);
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $fees = $query->paginate(20)->withQueryString();

        $allFees = StudentFee::all();
        $summary = [
            'total' => $allFees->sum('total_amount'),
            'collected' => $allFees->sum('paid_amount'),
            'pending' => $allFees->where('status', '!=', 'paid')->sum('pending_amount'),
            'unpaid' => $allFees->where('status', 'pending')->sum('total_amount'),
        ];

        return view('accountant.fees.index', compact('fees', 'summary'));
    }

    public function update(Request $request, StudentFee $fee)
    {
        $request->validate([
            'paid_amount' => 'required|numeric|min:0|max:' . $fee->total_amount,
            'receipt_number' => 'nullable|string|max:50|unique:payments,receipt_number',
            'remarks' => 'nullable|string|max:255',
        ]);

        $newPaidAmount = (float) $request->paid_amount;
        $previousPaid = (float) $fee->paid_amount;
        if ($newPaidAmount < $previousPaid) {
            return back()->withErrors(['paid_amount' => 'Paid amount cannot be reduced.']);
        }

        $delta = $newPaidAmount - $previousPaid;
        if ($delta > 0) {
            $payment = $this->feeService->recordPayment($fee, [
                'amount' => $delta,
                'payment_mode' => 'cash',
                'remarks' => $request->input('remarks'),
                'collected_by' => auth()->id(),
            ]);

            if ($request->filled('receipt_number')) {
                $payment->update(['receipt_number' => $request->input('receipt_number')]);
            }
        }

        if ($delta == 0) {
            $fee->paid_amount = $newPaidAmount;
            $fee->updatePaymentStatus();
        }

        return redirect()->route('accountant.fees.index')
            ->with('success', 'Fee payment registered successfully.');
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
