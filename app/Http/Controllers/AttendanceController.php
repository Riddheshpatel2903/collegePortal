<?php

// app/Http/Controllers/AttendanceController.php

namespace App\Http\Controllers;

use App\Models\AttendanceSession;
use App\Models\SemesterSubject;
use App\Models\Student;
use App\Services\AttendanceService;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    protected $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    /**
     * Show attendance marking form
     */
    public function create()
    {
        $teacher = auth()->user()->teacher;

        if (! $teacher) {
            abort(403, 'Teacher profile not found.');
        }

        // Get teacher's assigned subjects
        $assignments = \App\Models\TeacherSubjectAssignment::where('teacher_id', $teacher->id)
            ->with(['semesterSubject.subject', 'semester'])
            ->where('is_active', true)
            ->get();

        return view('attendance.create', compact('assignments'));
    }

    /**
     * Get students for attendance
     */
    public function getStudents(Request $request)
    {
        $semesterSubjectId = $request->semester_subject_id;

        $semesterSubject = SemesterSubject::with('semester')->findOrFail($semesterSubjectId);

        $students = Student::where('current_semester_id', $semesterSubject->semester_id)
            ->where('student_status', 'active')
            ->orderBy('roll_number')
            ->get();

        return response()->json(['students' => $students]);
    }

    /**
     * Store attendance
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'semester_subject_id' => 'required|exists:semester_subjects,id',
            'date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'nullable',
            'session_type' => 'required|in:lecture,practical,tutorial',
            'topic' => 'nullable|string',
            'attendance' => 'required|array',
        ]);

        $teacher = auth()->user()->teacher;

        // Create attendance session
        $session = $this->attendanceService->createSession([
            'semester_subject_id' => $validated['semester_subject_id'],
            'teacher_id' => $teacher->id,
            'date' => $validated['date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'session_type' => $validated['session_type'],
            'topic' => $validated['topic'],
        ]);

        // Mark attendance
        $this->attendanceService->markAttendance($session, $validated['attendance']);

        return redirect()->route('attendance.index')
            ->with('success', 'Attendance marked successfully.');
    }

    /**
     * Display attendance sessions
     */
    public function index(Request $request)
    {
        $query = AttendanceSession::with(['semesterSubject.subject', 'teacher']);

        if (auth()->user()->hasRole('teacher')) {
            $query->where('teacher_id', auth()->user()->teacher->id);
        }

        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }

        if ($request->filled('semester_subject_id')) {
            $query->where('semester_subject_id', $request->semester_subject_id);
        }

        $sessions = $query->latest('date')->paginate(15);

        return view('attendance.index', compact('sessions'));
    }

    /**
     * Show attendance session details
     */
    public function show(AttendanceSession $session)
    {
        $session->load(['attendances.student', 'semesterSubject.subject', 'teacher']);

        return view('attendance.show', compact('session'));
    }

    /**
     * Student attendance report
     */
    public function studentReport()
    {
        $student = auth()->user()->student;

        if (! $student) {
            abort(403, 'Student profile not found.');
        }

        $report = $this->attendanceService->getStudentAttendanceReport($student);

        return view('attendance.student-report', compact('report', 'student'));
    }
}
