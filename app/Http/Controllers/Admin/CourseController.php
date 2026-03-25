<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\Department;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $query = Course::with('department');
        if ($request->filled('search')) {
            $search = trim((string) $request->string('search'));
            $query->where('name', 'like', "%{$search}%")
                ->orWhereHas('department', fn ($q) => $q->where('name', 'like', "%{$search}%"));
        }
        $courses = $query->orderBy('name')->paginate(20)->withQueryString();
        return view('admin.courses.index', compact('courses'));
    }

    public function create()
    {
        $departments = Department::all();
        return view('admin.courses.create', compact('departments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'department_id' => 'required',
            'duration_years' => 'required|integer',
            'semesters_per_year' => 'nullable|integer|min:1|max:3',
        ]);

        Course::create($request->only(['name', 'department_id', 'duration_years', 'semesters_per_year', 'description', 'code']) + [
            'semesters_per_year' => (int) ($request->semesters_per_year ?: 2),
        ]);

        return redirect()->route('admin.courses.index')
            ->with('success', 'Course created successfully.');
    }

    public function edit(Course $course)
    {
        $departments = Department::all();
        return view('admin.courses.edit', compact('course', 'departments'));
    }

    public function update(Request $request, Course $course)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'duration_years' => 'required|integer|min:1|max:8',
            'semesters_per_year' => 'nullable|integer|min:1|max:3',
        ]);

        $course->update($request->only(['name', 'department_id', 'duration_years', 'semesters_per_year', 'description', 'code']) + [
            'semesters_per_year' => (int) ($request->semesters_per_year ?: 2),
        ]);

        return redirect()->route('admin.courses.index')
            ->with('success', 'Course updated successfully.');
    }

    public function destroy(Course $course)
    {
        $course->delete();

        return redirect()->route('admin.courses.index')
            ->with('success', 'Course deleted successfully.');
    }
}
