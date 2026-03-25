<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Teacher;
use App\Models\User;
use App\Models\Department;
use Illuminate\Support\Facades\Hash;

class TeacherController extends Controller
{
    public function index(Request $request)
    {
        $query = Teacher::with('user', 'department')
            ->withCount(['subjects', 'assignments'])
            ->orderByDesc('id');

        if ($request->filled('search')) {
            $search = trim((string) $request->string('search'));
            $query->whereHas('user', fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
        }

        $teachers = $query->paginate(20)->withQueryString();
        return view('admin.teachers.index', compact('teachers'));
    }

    public function create()
    {
        $departments = Department::all();
        return view('admin.teachers.create', compact('departments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'department_id' => 'required|exists:departments,id',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'teacher',
            'status' => 'active',
        ]);

        Teacher::create([
            'user_id' => $user->id,
            'department_id' => $request->department_id,
            'qualification' => $request->qualification,
            'phone' => $request->phone,
        ]);

        return redirect()->route('admin.teachers.index')
            ->with('success', 'Teacher created successfully.');
    }

    public function edit(Teacher $teacher)
    {
        $teacher->load('user', 'department');
        $departments = Department::all();

        return view('admin.teachers.edit', compact('teacher', 'departments'));
    }
    public function update(Request $request, Teacher $teacher)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $teacher->user_id,
            'department_id' => 'required|exists:departments,id',
        ]);

        $teacher->user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        $teacher->update($request->only([
            'department_id',
            'qualification',
            'phone'
        ]));

        return redirect()->route('admin.teachers.index')
            ->with('success', 'Teacher updated successfully.');
    }

    public function destroy(Teacher $teacher)
    {
        $teacher->user->delete();
        $teacher->delete();

        return redirect()->route('admin.teachers.index')
            ->with('success', 'Teacher deleted successfully.');
    }
}
