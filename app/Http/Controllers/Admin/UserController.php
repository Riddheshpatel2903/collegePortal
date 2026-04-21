<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Role;
use App\Models\User;
use App\Services\SemesterCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function __construct(private SemesterCalculationService $semesterCalculationService) {}

    private function ensureDefaultRoles(): void
    {
        $roles = collect(config('portal_access.roles', []))
            ->filter()
            ->map(fn ($name) => strtolower((string) $name))
            ->unique();

        foreach ($roles as $roleName) {
            Role::query()->firstOrCreate(['name' => $roleName]);
        }
    }

    public function index(Request $request)
    {
        $this->ensureDefaultRoles();
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

        $users = $query->latest()->paginate(20)->withQueryString();
        $roles = Role::query()->orderBy('name')->get();

        return view('admin.users.index', compact('users', 'roles'));
    }

    public function create()
    {
        $this->ensureDefaultRoles();
        $courses = \App\Models\Course::all();
        $departments = \App\Models\Department::all();
        $roles = Role::query()->orderBy('name')->get();

        return view('admin.users.create', compact('courses', 'departments', 'roles'));
    }

    public function store(Request $request)
    {
        $this->ensureDefaultRoles();
        $roleName = str_replace(' ', '_', strtolower((string) $request->input('role')));
        $rules = [
            'name' => 'required|string|regex:/^[a-zA-Z\s.]+$/|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'phone' => 'nullable|digits:10',
            'role' => ['required', 'string', 'max:50', 'regex:/^[a-zA-Z0-9_ ]+$/'],
        ];

        // Add role-specific validation
        if ($roleName === 'student') {
            $rules['roll_number'] = 'required|unique:students,roll_number';
            $rules['gtu_enrollment_no'] = 'required|string|max:50|unique:students,gtu_enrollment_no';
            $rules['semester_number'] = 'required|integer|min:1|max:20';
            $rules['course_id'] = 'required|exists:courses,id';
        }

        $request->validate($rules);

        \Illuminate\Support\Facades\DB::transaction(function () use ($request, $roleName) {
            Role::query()->firstOrCreate(['name' => $roleName]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $roleName,
                'status' => 'active',
            ]);

            if ($roleName === 'student') {
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

            if ($roleName === 'teacher') {
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
        $this->ensureDefaultRoles();
        $user->load(['student', 'teacher']);
        $courses = \App\Models\Course::all();
        $departments = \App\Models\Department::all();
        $roles = Role::query()->orderBy('name')->get();

        return view('admin.users.edit', compact('user', 'courses', 'departments', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $this->ensureDefaultRoles();
        $roleName = str_replace(' ', '_', strtolower((string) $request->input('role')));
        $rules = [
            'name' => 'required|string|regex:/^[a-zA-Z\s.]+$/|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'phone' => 'nullable|digits:10',
            'role' => ['required', 'string', 'max:50', 'regex:/^[a-zA-Z0-9_ ]+$/'],
        ];

        if ($roleName === 'student') {
            $rules['roll_number'] = 'required|unique:students,roll_number,'.($user->student->id ?? 'NULL');
            $rules['gtu_enrollment_no'] = 'required|string|max:50|unique:students,gtu_enrollment_no,'.($user->student->id ?? 'NULL');
            $rules['semester_number'] = 'required|integer|min:1|max:20';
            $rules['course_id'] = 'required|exists:courses,id';
        }

        $request->validate($rules);

        \Illuminate\Support\Facades\DB::transaction(function () use ($request, $user, $roleName) {
            Role::query()->firstOrCreate(['name' => $roleName]);
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'role' => $roleName,
            ]);

            if ($request->filled('password')) {
                $user->update(['password' => Hash::make($request->password)]);
            }

            // Handle student profile
            if ($roleName === 'student') {
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
            if ($roleName === 'teacher') {
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
