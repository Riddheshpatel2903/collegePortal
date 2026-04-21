<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Leave;
use App\Services\LeaveWorkflowService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeaveController extends Controller
{
    public function __construct(private LeaveWorkflowService $leaveWorkflowService) {}

    /**
     * Display leave application form and student's leave history.
     */
    public function index()
    {
        $student = Auth::user()->student;
        $leaves = $student->leaves()
            ->with('approver')
            ->latest('applied_at')
            ->get();

        return view('student.leaves.index', compact('leaves'));
    }

    /**
     * Store a new leave application.
     */
    public function store(Request $request)
    {
        $request->validate([
            'leave_type' => 'required|in:sick,casual,emergency,other',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|min:10',
        ]);

        $student = Auth::user()->student;
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $totalDays = $startDate->diffInDays($endDate) + 1;

        // 🛑 Overlap Prevention Logic
        $overlap = $student->leaves()
            ->whereIn('status', ['pending', 'approved'])
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($q) use ($startDate, $endDate) {
                        $q->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                    });
            })
            ->exists();

        if ($overlap) {
            return back()->withErrors(['error' => 'You already have a pending or approved leave for these dates.'])->withInput();
        }

        $leave = $student->leaves()->create([
            'leave_type' => $request->leave_type,
            'reason' => $request->reason,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => 'pending',
            'applied_at' => now(),
        ]);

        $this->leaveWorkflowService->submit($leave, Auth::user());

        return redirect()->route('student.leaves.index')->with('success', 'Leave application submitted successfully.');
    }
}
