<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Services\LeaveWorkflowService;
use Illuminate\Http\Request;

class LeaveController extends Controller
{
    public function __construct(private LeaveWorkflowService $leaveWorkflowService)
    {
    }

    public function index()
    {
        $teacher = auth()->user()->teacher;
        $leaves = $teacher?->leaves()->latest()->paginate(15);

        return view('teacher.leaves.index', compact('leaves'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'leave_type' => 'required|string|max:50',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|min:10',
        ]);

        $leave = auth()->user()->teacher->leaves()->create($validated + [
            'status' => 'pending',
        ]);

        $this->leaveWorkflowService->submit($leave, auth()->user());

        return back()->with('success', 'Leave request submitted to HOD.');
    }
}
