<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\User;
use App\Models\Course;
use App\Models\Department;
use App\Services\SemesterCalculationService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StudentController extends Controller
{
    public function __construct(private SemesterCalculationService $semesterCalculationService)
    {
    }

    public function index()
    {
        $departments = Department::all();

        // Stats for the Dashboard
        $stats = [
            'total' => Student::count(),
            'active' => Student::where('is_active', true)->count(),
            'new' => Student::where('created_at', '>=', now()->startOfMonth())->count(),
            'inactive' => Student::where('is_active', false)->count(),
        ];

        return view('admin.students.index', compact('departments', 'stats'));
    }

    /**
     * Legacy endpoint now returns available academic years for selected department courses.
     */
    public function getSemestersByDepartment(Request $request)
    {
        if (!$request->ajax() && !$request->wantsJson() && $request->header('X-Requested-With') !== 'XMLHttpRequest') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $maxDuration = Course::query()
            ->when($request->filled('department_id'), function ($q) use ($request) {
                $q->where('department_id', $request->department_id);
            })
            ->max('duration_years') ?? 4;

        $years = collect(range(1, (int) $maxDuration))->map(fn($year) => [
            'id' => $year,
            'name' => "Year {$year}",
        ])->values();

        return response()->json($years);
    }


    /**
     * Fetch students via AJAX for performance
     */
    public function fetchStudents(Request $request)
    {
        // Relaxing the strict check to allow for different request configurations
        if (!$request->ajax() && $request->header('X-Requested-With') !== 'XMLHttpRequest' && !$request->wantsJson()) {
            // Keep it for security but maybe log it?
            // \Log::warning('Non-AJAX access to fetchStudents');
        }

        $query = Student::with(['user', 'course.department'])
            ->whereHas('user');

        // SEARCH
        if ($request->filled('search')) {
            $query->search((string) $request->search);
        }

        // DEPARTMENT FILTER
        if ($request->filled('department_id')) {
            $query->whereHas('course', function ($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        }

        // YEAR FILTER
        if ($request->filled('current_year')) {
            $query->where('current_year', $request->current_year);
        }

        // STATUS FILTER
        if ($request->filled('status') || $request->status === '0') {
            $query->where('is_active', $request->status);
        }

        $students = $query->orderBy('current_year')
            ->orderBy('roll_number')
            ->paginate(20);

        $studentsGrouped = $students->groupBy(function ($student) {
            return 'Year ' . ($student->current_year ?: 1);
        });

        $view = view('admin.students.partials.table', [
            'studentsGrouped' => $studentsGrouped,
            'studentsPaginated' => $students
        ])->render();

        return response($view);
    }

    /**
     * Get courses for a department
     */
    public function getDepartmentCourses($id)
    {
        return response()->json(Course::where('department_id', $id)->get());
    }

    public function create()
    {
        $courses = Course::all();
        return view('admin.students.create', compact('courses'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|regex:/^[a-zA-Z\s.]+$/|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:6',
                'roll_number' => 'required|unique:students,roll_number',
                'gtu_enrollment_no' => 'required|string|max:50|unique:students,gtu_enrollment_no',
                'course_id' => 'required|exists:courses,id',
                'current_year' => 'nullable|integer|min:1|max:10',
                'admission_year' => 'nullable|integer|min:2000|max:2100',
                'phone' => 'nullable|digits:10',
                'address' => 'nullable|string|max:1000',
            ]);
            $this->validateYearWithinCourse((int) $validated['course_id'], (int) ($validated['current_year'] ?? 1));

            DB::transaction(function () use ($validated) {
                $user = User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['password']),
                    'role' => 'student',
                    'status' => 'active',
                ]);

                Student::create([
                    'user_id' => $user->id,
                    'roll_number' => $validated['roll_number'],
                    'gtu_enrollment_no' => strtoupper(trim($validated['gtu_enrollment_no'])),
                    'course_id' => $validated['course_id'],
                    'current_year' => $validated['current_year'] ?? 1,
                    'admission_year' => $validated['admission_year'] ?? now()->year,
                    'phone' => $validated['phone'] ?? null,
                    'address' => $validated['address'] ?? null,
                    'student_status' => 'active',
                    'academic_status' => 'active',
                ]);
            });
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['errors' => $e->errors()], 422);
            }
            throw $e;
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Student enrolled successfully.',
                'redirect' => route('admin.students.index'),
            ]);
        }

        return redirect()->route('admin.students.index')
            ->with('success', 'Student created successfully.');
    }

    public function edit(Student $student)
    {
        $student->load('user');
        $courses = Course::all();
        return view('admin.students.edit', compact('student', 'courses'));
    }

    public function update(Request $request, Student $student)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|regex:/^[a-zA-Z\s.]+$/|max:255',
                'email' => 'required|email|unique:users,email,' . $student->user_id,
                'roll_number' => 'required|unique:students,roll_number,' . $student->id,
                'gtu_enrollment_no' => 'required|string|max:50|unique:students,gtu_enrollment_no,' . $student->id,
                'course_id' => 'required|exists:courses,id',
                'current_year' => 'nullable|integer|min:1|max:10',
                'admission_year' => 'nullable|integer|min:2000|max:2100',
                'phone' => 'nullable|digits:10',
                'address' => 'nullable|string|max:1000',
            ]);
            $this->validateYearWithinCourse((int) $validated['course_id'], (int) ($validated['current_year'] ?? $student->current_year ?? 1));

            DB::transaction(function () use ($validated, $request, $student) {
                $student->user->update([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                ]);

                $student->update([
                    'roll_number' => $validated['roll_number'],
                    'gtu_enrollment_no' => strtoupper(trim($validated['gtu_enrollment_no'])),
                    'course_id' => $validated['course_id'],
                    'current_year' => $validated['current_year'] ?? $student->current_year,
                    'admission_year' => $validated['admission_year'] ?? $student->admission_year,
                    'phone' => $validated['phone'] ?? null,
                    'address' => $validated['address'] ?? null,
                    'is_active' => $request->has('is_active') ? true : false,
                ]);
            });
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['errors' => $e->errors()], 422);
            }
            throw $e;
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Student updated successfully.',
            ]);
        }

        return redirect()->route('admin.students.index')
            ->with('success', 'Student updated successfully.');
    }

    private function validateYearWithinCourse(int $courseId, int $currentYear): void
    {
        $course = Course::findOrFail($courseId);
        if ($currentYear < 1 || $currentYear > (int) $course->duration_years) {
            throw ValidationException::withMessages([
                'current_year' => "Current year must be between 1 and {$course->duration_years} for {$course->name}.",
            ]);
        }
    }

    public function toggleStatus(Request $request, Student $student)
    {
        $student->update(['is_active' => !$student->is_active]);

        $status = $student->is_active ? 'activated' : 'deactivated';

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'is_active' => $student->is_active,
                'message' => "Student has been {$status} successfully.",
            ]);
        }

        return redirect()->back()->with('success', "Student has been {$status} successfully.");
    }

    public function destroy(Request $request, Student $student)
    {
        $user = $student->user;
        $student->delete();
        if ($user) {
            $user->delete();
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Student deleted successfully.',
            ]);
        }

        return redirect()->route('admin.students.index')
            ->with('success', 'Student deleted successfully.');
    }
}
