<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AssignmentController extends Controller
{
    /**
     * Display a global overview of all assignments.
     */
    public function adminIndex()
    {
        $stats = [
            'total' => Assignment::count(),
            'active' => Assignment::where('status', 'published')->where('is_active', true)->count(),
            'late_submissions' => AssignmentSubmission::where('status', 'late')->count(),
            'submission_rate' => Assignment::count() > 0
                ? round((AssignmentSubmission::count() / (Assignment::count() * 10)) * 100, 1) // Rough estimation
                : 0,
        ];

        $assignments = Assignment::with(['teacher.user', 'subject', 'course', 'semester'])
            ->withCount('submissions')
            ->latest()
            ->paginate(20);

        // Subject-wise performance (simplified)
        $subjectPerformance = AssignmentSubmission::select('subjects.name', DB::raw('count(*) as count'))
            ->join('assignments', 'assignment_submissions.assignment_id', '=', 'assignments.id')
            ->join('subjects', 'assignments.subject_id', '=', 'subjects.id')
            ->groupBy('subjects.name')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get();

        return view('admin.assignments.index', compact('stats', 'assignments', 'subjectPerformance'));
    }

    /**
     * Force close an assignment.
     */
    public function forceClose($id)
    {
        $assignment = Assignment::findOrFail($id);
        $assignment->update([
            'status' => 'closed',
            'is_active' => false
        ]);

        return back()->with('success', 'Assignment marked as closed.');
    }

    /**
     * Extend the deadline of an assignment.
     */
    public function extendDeadline(Request $request, $id)
    {
        $request->validate([
            'new_due_date' => 'required|date|after:now',
        ]);

        $assignment = Assignment::findOrFail($id);
        $assignment->update([
            'due_date' => $request->new_due_date,
            'status' => 'published',
            'is_active' => true
        ]);

        return back()->with('success', 'Deadline extended successfully.');
    }

    /**
     * Remove the specified assignment from storage.
     */
    public function destroy($id)
    {
        $assignment = Assignment::findOrFail($id);

        if ($assignment->attachment_path) {
            Storage::disk('public')->delete($assignment->attachment_path);
        }

        $assignment->delete();

        return back()->with('success', 'Assignment deleted from the system.');
    }
}
