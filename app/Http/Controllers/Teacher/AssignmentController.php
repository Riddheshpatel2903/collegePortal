<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Subject;
use App\Models\TeacherSubjectAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AssignmentController extends Controller
{
    /**
     * Display a listing of assignments created by the teacher.
     */
    public function index()
    {
        $teacher = auth()->user()->teacher;

        if (! $teacher) {
            return redirect()->route('teacher.dashboard')->with('error', 'Teacher profile not found.');
        }

        $assignments = Assignment::with(['subject', 'course', 'semester'])
            ->withCount('submissions')
            ->where('teacher_id', $teacher->id)
            ->latest()
            ->paginate(12);

        return view('teacher.assignments.index', compact('assignments'));
    }

    /**
     * Show the form for creating a new assignment.
     */
    public function create()
    {
        $teacher = auth()->user()->teacher;

        if (! $teacher) {
            return redirect()->route('teacher.dashboard')->with('error', 'Teacher profile not found.');
        }

        $subjects = $this->assignedSubjects($teacher->id);

        return view('teacher.assignments.create', compact('subjects'));
    }

    /**
     * Store a newly created assignment in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'title' => 'required|string|min:5|max:255',
            'description' => 'required|string|min:20',
            'total_marks' => 'required|integer|min:1',
            'due_date' => 'required|date|after:now',
            'attachment' => 'nullable|file|mimes:pdf,doc,docx,zip,jpg,png|max:10240',
            'status' => 'required|in:draft,published',
            'allow_late_submission' => 'boolean',
            'late_until' => 'nullable|date|after:due_date',
        ]);

        $teacherId = auth()->user()->teacher->id;
        $subject = $this->findAssignedSubject($teacherId, (int) $request->subject_id);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('assignments/attachments', 'public');
        }

        $semestersPerYear = max(1, (int) ($subject->course->semesters_per_year ?? 2));
        $academicYear = (int) ceil(((int) $subject->semester_sequence) / $semestersPerYear);

        Assignment::create([
            'teacher_id' => $teacherId,
            'subject_id' => $subject->id,
            'course_id' => $subject->course_id,
            'academic_year' => $academicYear,
            'semester_number' => (int) $subject->semester_sequence,
            'semester_id' => null,
            'title' => $request->title,
            'description' => $request->description,
            'total_marks' => $request->total_marks,
            'due_date' => $request->due_date,
            'attachment_path' => $attachmentPath,
            'status' => $request->status,
            'allow_late_submission' => $request->boolean('allow_late_submission'),
            'late_until' => $request->late_until,
            'is_active' => true,
        ]);

        return redirect()->route('teacher.assignments.index')
            ->with('success', 'Assignment created and '.($request->status == 'published' ? 'published' : 'saved as draft').'.');
    }

    /**
     * Show the form for editing the specified assignment.
     */
    public function edit($id)
    {
        $teacherId = auth()->user()->teacher->id;
        $assignment = Assignment::where('teacher_id', $teacherId)->findOrFail($id);

        $subjects = $this->assignedSubjects($teacherId);

        return view('teacher.assignments.edit', compact('assignment', 'subjects'));
    }

    /**
     * Update the specified assignment in storage.
     */
    public function update(Request $request, $id)
    {
        $teacherId = auth()->user()->teacher->id;
        $assignment = Assignment::where('teacher_id', $teacherId)->findOrFail($id);

        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'title' => 'required|string|min:5|max:255',
            'description' => 'required|string|min:20',
            'total_marks' => 'required|integer|min:1',
            'due_date' => 'required|date',
            'attachment' => 'nullable|file|mimes:pdf,doc,docx,zip,jpg,png|max:10240',
            'status' => 'required|in:draft,published',
            'allow_late_submission' => 'boolean',
            'late_until' => 'nullable|date|after:due_date',
        ]);

        $subject = $this->findAssignedSubject($teacherId, (int) $request->subject_id);

        if ($request->hasFile('attachment')) {
            // Delete old attachment if exists
            if ($assignment->attachment_path) {
                Storage::disk('public')->delete($assignment->attachment_path);
            }
            $assignment->attachment_path = $request->file('attachment')->store('assignments/attachments', 'public');
        }

        $semestersPerYear = max(1, (int) ($subject->course->semesters_per_year ?? 2));
        $academicYear = (int) ceil(((int) $subject->semester_sequence) / $semestersPerYear);

        $assignment->update([
            'subject_id' => $subject->id,
            'course_id' => $subject->course_id,
            'academic_year' => $academicYear,
            'semester_number' => (int) $subject->semester_sequence,
            'semester_id' => null,
            'title' => $request->title,
            'description' => $request->description,
            'total_marks' => $request->total_marks,
            'due_date' => $request->due_date,
            'status' => $request->status,
            'allow_late_submission' => $request->boolean('allow_late_submission'),
            'late_until' => $request->late_until,
        ]);

        return redirect()->route('teacher.assignments.index')
            ->with('success', 'Assignment updated successfully.');
    }

    /**
     * Remove the specified assignment from storage.
     */
    public function destroy($id)
    {
        $teacherId = auth()->user()->teacher->id;
        $assignment = Assignment::where('teacher_id', $teacherId)->findOrFail($id);

        if ($assignment->attachment_path) {
            Storage::disk('public')->delete($assignment->attachment_path);
        }

        $assignment->delete();

        return back()->with('success', 'Assignment deleted.');
    }

    /**
     * Display submissions for a specific assignment.
     */
    public function submissions($id)
    {
        $teacherId = auth()->user()->teacher->id;
        $assignment = Assignment::where('teacher_id', $teacherId)
            ->with(['subject', 'course', 'semester'])
            ->findOrFail($id);

        $submissions = AssignmentSubmission::where('assignment_id', $id)
            ->with('student.user')
            ->latest('submitted_at')
            ->get();

        return view('teacher.assignments.submissions', compact('assignment', 'submissions'));
    }

    /**
     * Grade a specific submission.
     */
    public function grade(Request $request, $id)
    {
        $request->validate([
            'marks_obtained' => 'required|integer|min:0',
            'feedback' => 'nullable|string',
        ]);

        $submission = AssignmentSubmission::findOrFail($id);

        // Verify teacher owns the assignment
        if ($submission->assignment->teacher_id !== auth()->user()->teacher->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($request->marks_obtained > $submission->assignment->total_marks) {
            return response()->json(['error' => 'Marks cannot exceed total marks'], 422);
        }

        $submission->update([
            'marks_obtained' => $request->marks_obtained,
            'feedback' => $request->feedback,
            'status' => 'graded',
        ]);

        if ($request->ajax()) {
            return response()->json(['success' => 'Grade saved successfully', 'status' => 'graded']);
        }

        return back()->with('success', 'Grade saved successfully.');
    }

    private function assignedSubjects(int $teacherId)
    {
        $subjects = Subject::query()
            ->whereIn('id', function ($q) use ($teacherId) {
                $q->select('subject_id')
                    ->from('teacher_subject_assignments')
                    ->where('teacher_id', $teacherId)
                    ->where('is_active', true);
            })
            ->with('course')
            ->orderBy('name')
            ->get();

        return $subjects->map(function (Subject $subject) {
            $subject->semester_label = 'Semester '.(int) $subject->semester_sequence;

            return $subject;
        });
    }

    private function findAssignedSubject(int $teacherId, int $subjectId): Subject
    {
        $allowed = TeacherSubjectAssignment::query()
            ->where('teacher_id', $teacherId)
            ->where('subject_id', $subjectId)
            ->where('is_active', true)
            ->exists();

        abort_unless($allowed, 403, 'You can only manage assignments for assigned subjects.');

        return Subject::with('course')->findOrFail($subjectId);
    }
}
