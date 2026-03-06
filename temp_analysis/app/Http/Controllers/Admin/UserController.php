<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Course;
use Illuminate\Support\Facades\Hash;
use App\Services\SemesterCalculationService;

class UserController extends Controller
{
    public function __construct(private SemesterCalculationService $semesterCalculationService)
    {
    }

    public function index(Request $request)
    {
        $query = User::with(['student.course', 'student.currentSemester', 'teacher.department']);

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('email', 'like', "%{$searchTerm}%")
                  ->orWhere('id', 'like', "%{$searchTerm}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $users = $query->latest()->paginate(15)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $courses = \App\Models\Course::all();
        $departments = \App\Models\Department::all();
        return view('admin.users.create', compact('courses', 'departments'));
    }

    public function store(Request $request)
    {
        $rules = [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role' => 'required|in:admin,hod,teacher,student,accountant',
        ];

        // Add role-specific validation
        if ($request->role === 'student') {
            $rules['roll_number'] = 'required|unique:students,roll_number';
            $rules['gtu_enrollment_no'] = 'required|string|max:50|unique:students,gtu_enrollment_no';
            $rules['semester_number'] = 'required|integer|min:1|max:20';
            $rules['course_id'] = 'required|exists:courses,id';
        }

        $request->validate($rules);

        \Illuminate\Support\Facades\DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
                'status' => 'active',
            ]);

            if ($request->role === 'student') {
                $course = Course::findOrFail((int) $request->course_id);
                $semesterNumber = (int) $request->semester_number;
                $this->semesterCalculationService->validateSemesterWithinCourse($course, $semesterNumber);
                $currentYear = $this->semesterCalculationService->yearFromSemester($course, $semesterNumber);

                $user->student()->create([
                    'roll_number' => $request->roll_number,
                    'gtu_enrollment_no' => strtoupper(trim((string) $request->gtu_enrollment_no)),
                    'course_id' => $request->course_id,
                    'current_semester_id' => null,
                    'current_year' => $currentYear,
                    'admission_year' => $request->admission_year,
                    'phone' => $request->phone,
                    'address' => $request->address,
                ]);
            }

            if ($request->role === 'teacher') {
                $user->teacher()->create([
                    'department_id' => $request->department_id,
                    'qualification' => $request->qualification,
                    'phone' => $request->phone,
                ]);
            }
        });

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        $user->load(['student', 'teacher']);
        $courses = \App\Models\Course::all();
        $departments = \App\Models\Department::all();
        return view('admin.users.edit', compact('user', 'courses', 'departments'));
    }

    public function update(Request $request, User $user)
    {
        $rules = [
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,hod,teacher,student,accountant',
        ];

        if ($request->role === 'student') {
            $rules['roll_number'] = 'required|unique:students,roll_number,' . ($user->student->id ?? 'NULL');
            $rules['gtu_enrollment_no'] = 'required|string|max:50|unique:students,gtu_enrollment_no,' . ($user->student->id ?? 'NULL');
            $rules['semester_number'] = 'required|integer|min:1|max:20';
            $rules['course_id'] = 'required|exists:courses,id';
        }

        $request->validate($rules);

        \Illuminate\Support\Facades\DB::transaction(function () use ($request, $user) {
            $user->update($request->only('name', 'email', 'role'));

            if ($request->filled('password')) {
                $user->update(['password' => Hash::make($request->password)]);
            }

            // Handle student profile
            if ($request->role === 'student') {
                $course = Course::findOrFail((int) $request->course_id);
                $semesterNumber = (int) $request->semester_number;
                $this->semesterCalculationService->validateSemesterWithinCourse($course, $semesterNumber);
                $currentYear = $this->semesterCalculationService->yearFromSemester($course, $semesterNumber);

                $user->student()->updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'roll_number' => $request->roll_number,
                        'gtu_enrollment_no' => strtoupper(trim((string) $request->gtu_enrollment_no)),
                        'course_id' => $request->course_id,
                        'current_semester_id' => null,
                        'current_year' => $currentYear,
                        'admission_year' => $request->admission_year,
                        'phone' => $request->phone,
                        'address' => $request->address,
                    ]
                );
            }

            // Handle teacher profile
            if ($request->role === 'teacher') {
                $user->teacher()->updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'department_id' => $request->department_id,
                        'qualification' => $request->qualification,
                        'phone' => $request->phone,
                    ]
                );
            }
        });

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }
}
