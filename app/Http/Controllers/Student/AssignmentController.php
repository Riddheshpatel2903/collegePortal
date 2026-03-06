<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Course;
use Illuminate\Support\Facades\Storage;

class AssignmentController extends Controller
{
    /**
     * Display assignments relevant to the student.
     */
    public function index()
    {
        $user = auth()->user();
        $student = $user->student()->with(['course', 'currentSemester'])->first();

        if (!$student) {
            return view('student.assignments.index', ['assignments' => collect()]);
        }

        $course = Course::find($student->course_id);
        $semestersPerYear = max(1, (int) ($course?->semesters_per_year ?? 2));
        $fromSemester = (($student->current_year - 1) * $semestersPerYear) + 1;
        $toSemester = $student->current_year * $semestersPerYear;

        // Get assignments for the student's course and current academic year semesters
        $assignments = Assignment::where('course_id', $student->course_id)
            ->whereBetween('semester_number', [$fromSemester, $toSemester])
            ->where('status', 'published')
            ->where('is_active', true)
            ->with(['subject', 'teacher.user'])
            ->with([
                'submissions' => function ($query) use ($student) {
                    $query->where('student_id', $student->id);
                }
            ])
            ->latest('due_date')
            ->paginate(12);

        return view('student.assignments.index', compact('assignments'));
    }

    /**
     * Submit an assignment.
     */
    public function submit(Request $request, $id)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf,doc,docx,zip|max:20480',
        ]);

        $assignment = Assignment::findOrFail($id);
        $student = auth()->user()->student;
        $this->assertAssignmentAllowedForStudent($assignment, $student);

        // Check if already submitted
        $existingSubmission = AssignmentSubmission::where('assignment_id', $assignment->id)
            ->where('student_id', $student->id)
            ->first();

        if ($existingSubmission) {
            return back()->with('error', 'You have already submitted this assignment.');
        }

        // Check if submission is allowed (deadline)
        if (!$assignment->isSubmissionAllowed()) {
            return back()->with('error', 'The deadline for this assignment has passed.');
        }

        $filePath = $request->file('file')->store('assignments/submissions', 'public');

        $status = $assignment->isLate() ? 'late' : 'submitted';

        AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'student_id' => $student->id,
            'file_path' => $filePath,
            'submitted_at' => now(),
            'status' => $status,
        ]);

        return redirect()->route('student.assignments.index')
            ->with('success', 'Assignment submitted successfully' . ($status == 'late' ? ' (Late Submission).' : '.'));
    }

    /**
     * Resubmit an assignment.
     */
    public function resubmit(Request $request, $id)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf,doc,docx,zip|max:20480',
        ]);

        $assignment = Assignment::findOrFail($id);
        $student = auth()->user()->student;
        $this->assertAssignmentAllowedForStudent($assignment, $student);

        $submission = AssignmentSubmission::where('assignment_id', $assignment->id)
            ->where('student_id', $student->id)
            ->firstOrFail();

        // Check if grading has already started
        if ($submission->status === 'graded') {
            return back()->with('error', 'Cannot resubmit an assignment that has already been graded.');
        }

        // Check if submission is allowed (deadline)
        if (!$assignment->isSubmissionAllowed()) {
            return back()->with('error', 'The deadline for this assignment has passed.');
        }

        // Delete old file
        Storage::disk('public')->delete($submission->file_path);

        $filePath = $request->file('file')->store('assignments/submissions', 'public');
        $status = $assignment->isLate() ? 'late' : 'submitted';

        $submission->update([
            'file_path' => $filePath,
            'submitted_at' => now(),
            'status' => $status,
        ]);

        return redirect()->route('student.assignments.index')
            ->with('success', 'Assignment resubmitted successfully.');
    }

    private function assertAssignmentAllowedForStudent(Assignment $assignment, $student): void
    {
        abort_unless($assignment->course_id === $student->course_id, 403, 'Assignment is not available for your course.');

        $course = Course::find($student->course_id);
        $semestersPerYear = max(1, (int) ($course?->semesters_per_year ?? 2));
        $fromSemester = (($student->current_year - 1) * $semestersPerYear) + 1;
        $toSemester = $student->current_year * $semestersPerYear;

        abort_unless(
            $assignment->semester_number >= $fromSemester && $assignment->semester_number <= $toSemester,
            403,
            'Assignment is not available for your current year.'
        );
    }
}
