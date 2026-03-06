<?php

namespace App\Http\Controllers\Hod;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Leave;
use App\Services\LeaveWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LeaveController extends Controller
{
    public function __construct(private LeaveWorkflowService $leaveWorkflowService)
    {
    }

    public function index(Request $request)
    {
        $department = Department::where('hod_id', auth()->id())->firstOrFail();

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', Rule::in(['pending', 'approved', 'rejected'])],
            'role' => ['nullable', Rule::in(['student', 'teacher', 'hod'])],
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
        ]);

        $leaves = Leave::query()
            ->withApplicantRelations()
            ->forDepartment($department->id)
            ->filter($filters)
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('hod.leaves.index', [
            'leaves' => $leaves,
            'filters' => $filters,
        ]);
    }

    public function approve(Leave $leave)
    {
        $this->ensureLeaveIsInHodDepartment($leave);
        $this->leaveWorkflowService->approve($leave, auth()->user());
        return back()->with('success', 'Leave forwarded/approved successfully.');
    }

    public function reject(Request $request, Leave $leave)
    {
        $this->ensureLeaveIsInHodDepartment($leave);
        $this->leaveWorkflowService->reject($leave, auth()->user(), $request->input('approval_remarks'));
        return back()->with('success', 'Leave rejected successfully.');
    }

    private function ensureLeaveIsInHodDepartment(Leave $leave): void
    {
        $departmentId = Department::where('hod_id', auth()->id())->value('id');
        abort_unless(
            Leave::query()
                ->whereKey($leave->id)
                ->forDepartment((int) $departmentId)
                ->exists(),
            403,
            'This leave does not belong to your department.'
        );
    }
}
