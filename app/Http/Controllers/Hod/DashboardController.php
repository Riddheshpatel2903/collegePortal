<?php

namespace App\Http\Controllers\Hod;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Leave;
use App\Models\Notice;
use App\Models\Student;
use App\Models\Teacher;

class DashboardController extends Controller
{
    public function index()
    {
        $department = Department::where('hod_id', auth()->id())->first();
        abort_unless((bool)$department, 403, 'HOD department mapping not found.');

        $teacherIds = Teacher::where('department_id', $department->id)->pluck('id');
        $studentIds = Student::where('department_id', $department->id)->pluck('id');

        $stats = [
            'teachers' => $teacherIds->count(),
            'students' => $studentIds->count(),
            'pending_leaves' => Leave::where('current_stage', 'hod_review')->where('status', 'pending')->count(),
            'active_notices' => Notice::where('department_id', $department->id)->where('is_active', true)->count(),
        ];

        // Fetch Department Notices
        $notices = Notice::where('department_id', $department->id)
            ->latest()
            ->take(5)
            ->get();

        // Fetch Pending Leaves (for quick review list)
        $pendingLeaves = Leave::withApplicantRelations()
            ->where('current_stage', 'hod_review')
            ->where('status', 'pending')
            ->latest()
            ->take(5)
            ->get();

        return view('hod.dashboard', compact('department', 'stats', 'notices', 'pendingLeaves'));
    }
}
