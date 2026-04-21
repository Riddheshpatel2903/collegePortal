<?php

// app/Http/Controllers/StudentController.php

namespace App\Http\Controllers;

use App\Models\AcademicSession;
use App\Models\Course;
use App\Models\Department;
use App\Models\Student;
use App\Services\AttendanceService;
use App\Services\ResultService;
use App\Services\SemesterCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class StudentController extends Controller
{
    protected $attendanceService;

    protected $resultService;

    protected $semesterCalculationService;

    public function __construct(AttendanceService $attendanceService, ResultService $resultService, SemesterCalculationService $semesterCalculationService)
    {
        $this->attendanceService = $attendanceService;
        $this->resultService = $resultService;
        $this->semesterCalculationService = $semesterCalculationService;
    }

    /**
     * Display a listing of the students.
     */
    public function index(Request $request)
    {
        $query = Student::with(['department', 'course', 'currentSemester']);

        // Filters
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        if ($request->filled('semester_id')) {
            $query->where('current_semester_id', $request->semester_id);
        }

        if ($request->filled('status')) {
            $query->where('student_status', $request->status);
        }

        if ($request->filled('search')) {
            $query->search((string) $request->search);
        }

        $students = $query->latest()->paginate(15);

        $departments = Department::all();
        $courses = Course::all();

        return view('students.index', compact('students', 'departments', 'courses'));
    }

    /**
     * Show the form for creating a new student.
     */
    public function create()
    {
        $departments = Department::all();
        $courses = Course::where('is_active', true)->get();
        $sessions = AcademicSession::where('status', 'active')->get();

        return view('students.create', compact('departments', 'courses', 'sessions'));
    }

    /**
     * Store a newly created student in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:students,email',
            'phone' => 'required|string|max:15',
            'department_id' => 'required|exists:departments,id',
            'course_id' => 'required|exists:courses,id',
            'academic_session_id' => 'required|exists:academic_sessions,id',
            'roll_number' => 'required|unique:students,roll_number',
            'gtu_enrollment_no' => 'required|string|max:50|unique:students,gtu_enrollment_no',
            'registration_number' => 'required|unique:students,registration_number',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:male,female,other',
            'address' => 'required|string',
            'admission_date' => 'required|date',
            'admission_year' => 'required|integer',
            'photo' => 'nullable|image|max:2048',
        ]);

        $course = Course::findOrFail((int) $validated['course_id']);
        $currentYear = (int) ($validated['current_year'] ?? 1);
        if ($currentYear < 1 || $currentYear > (int) $course->duration_years) {
            throw ValidationException::withMessages([
                'current_year' => "Current year must be between 1 and {$course->duration_years}.",
            ]);
        }

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('students', 'public');
        }

        $validated['student_status'] = 'active';

        $student = Student::create($validated);

        return redirect()->route('students.index')
            ->with('success', 'Student created successfully. Fees have been applied automatically.');
    }

    /**
     * Display the specified student.
     */
    public function show(Student $student)
    {
        $student->load([
            'department',
            'course',
            'currentSemester',
            'academicSession',
            'fees.feeStructure',
            'results.semester',
        ]);

        // Get attendance report
        $attendanceReport = $this->attendanceService->getStudentAttendanceReport($student);

        return view('students.show', compact('student', 'attendanceReport'));
    }

    /**
     * Show the form for editing the specified student.
     */
    public function edit(Student $student)
    {
        $departments = Department::all();
        $courses = Course::where('is_active', true)->get();
        $sessions = AcademicSession::all();

        return view('students.edit', compact('student', 'departments', 'courses', 'sessions'));
    }

    /**
     * Update the specified student in storage.
     */
    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:students,email,'.$student->id,
            'phone' => 'required|string|max:15',
            'department_id' => 'required|exists:departments,id',
            'course_id' => 'required|exists:courses,id',
            'gtu_enrollment_no' => 'required|string|max:50|unique:students,gtu_enrollment_no,'.$student->id,
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:male,female,other',
            'address' => 'required|string',
            'photo' => 'nullable|image|max:2048',
        ]);

        // Handle photo upload
        if ($request->hasFile('photo')) {
            // Delete old photo
            if ($student->photo) {
                Storage::disk('public')->delete($student->photo);
            }
            $validated['photo'] = $request->file('photo')->store('students', 'public');
        }

        $student->update($validated);

        return redirect()->route('students.show', $student)
            ->with('success', 'Student updated successfully.');
    }

    /**
     * Remove the specified student from storage.
     */
    public function destroy(Student $student)
    {
        // Delete photo
        if ($student->photo) {
            Storage::disk('public')->delete($student->photo);
        }

        $student->delete();

        return redirect()->route('students.index')
            ->with('success', 'Student deleted successfully.');
    }

    /**
     * Show student dashboard
     */
    public function dashboard()
    {
        $student = auth()->user()->student;

        if (! $student) {
            abort(403, 'Student profile not found.');
        }

        $student->load([
            'course',
            'currentSemester.semesterSubjects.subject',
            'fees' => function ($query) {
                $query->where('status', '!=', 'paid')->latest();
            },
        ]);

        // Calculate attendance percent
        $attendanceReport = $this->attendanceService->getStudentAttendanceReport($student);
        $totalSessions = collect($attendanceReport)->sum('total_classes');
        $attendedSessions = collect($attendanceReport)->sum('attended');
        $attendancePercent = $totalSessions > 0 ? round(($attendedSessions / $totalSessions) * 100, 2) : 0;

        // Pending fees
        $pendingFees = $student->fees->where('status', '!=', 'paid')->sum('pending_amount');

        // Subject count
        $subjectCount = $student->currentSemester ? $student->currentSemester->semesterSubjects->count() : 0;

        // Mocking assignments due for now as assignments table was truncated/restructured
        $assignmentsDue = 0;

        // Notices
        $notices = \App\Models\Notice::where('is_active', true)
            ->whereIn('notice_for', ['all', 'students'])
            ->latest()
            ->take(5)
            ->get();

        // Events
        $events = \App\Models\Notice::where('is_active', true)
            ->where('notice_for', 'all')
            ->where('priority', 'high')
            ->latest()
            ->take(5)
            ->get();

        $user = auth()->user();

        return view('student.dashboard', compact(
            'user',
            'student',
            'attendancePercent',
            'pendingFees',
            'assignmentsDue',
            'subjectCount',
            'notices',
            'events'
        ));
    }
}
