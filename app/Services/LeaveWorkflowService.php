<?php

namespace App\Services;

use App\Models\Leave;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LeaveWorkflowService
{
    public function __construct(private PortalAccessService $accessService)
    {
    }

    public function submit(Leave $leave, User $actor): Leave
    {
        $role = $actor->role;
        if (!in_array($role, ['student', 'teacher', 'hod'], true)) {
            throw ValidationException::withMessages(['leave' => 'Only student, teacher or HOD can apply leave.']);
        }

        return DB::transaction(function () use ($leave, $role) {
            $autoApproval = $this->accessService->featureEnabled('leave_auto_approval_toggle', false);
            $leave->update([
                'requested_by_role' => $role,
                'current_stage' => $autoApproval ? 'closed' : ($role === 'hod' ? 'admin_review' : 'hod_review'),
                'status' => $autoApproval ? 'approved' : 'pending',
                'applied_at' => now(),
                'approved_at' => $autoApproval ? now() : null,
                'approval_remarks' => $autoApproval ? 'Auto-approved by system setting.' : null,
            ]);
            return $leave;
        });
    }

    public function approve(Leave $leave, User $approver): Leave
    {
        return DB::transaction(function () use ($leave, $approver) {
            if ($leave->requested_by_role === 'hod') {
                $this->authorizeRole($approver, 'admin');
                return $this->close($leave, $approver, 'approved');
            }

            if ($leave->current_stage === 'hod_review') {
                $this->authorizeRole($approver, 'hod');
                $leave->update([
                    'current_stage' => 'admin_review',
                    'approved_by' => $approver->id,
                    'approval_remarks' => 'Forwarded by HOD',
                ]);
                return $leave->refresh();
            }

            $this->authorizeRole($approver, 'admin');
            return $this->close($leave, $approver, 'approved');
        });
    }

    public function reject(Leave $leave, User $approver, ?string $remarks = null): Leave
    {
        if (!in_array($approver->role, ['hod', 'admin'], true)) {
            throw ValidationException::withMessages(['leave' => 'Only HOD/Admin can reject leave.']);
        }

        return $this->close($leave, $approver, 'rejected', $remarks);
    }

    private function close(Leave $leave, User $approver, string $status, ?string $remarks = null): Leave
    {
        $leave->update([
            'status' => $status,
            'current_stage' => 'closed',
            'approved_by' => $approver->id,
            'approval_remarks' => $remarks,
            'approved_at' => now(),
        ]);

        return $leave->refresh();
    }

    private function authorizeRole(User $approver, string $role): void
    {
        if ($approver->role !== $role) {
            throw ValidationException::withMessages(['leave' => "Only {$role} can complete this action."]);
        }
    }
}
