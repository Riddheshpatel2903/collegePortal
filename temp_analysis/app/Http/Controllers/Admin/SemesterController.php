<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Semester;
use App\Models\Course;

class SemesterController extends Controller
{
    public function index()
    {
        $semesters = Semester::with('course')->paginate(10);
        return view('admin.semesters.index', compact('semesters'));
    }

    public function create()
    {
        $courses = Course::all();
        return view('admin.semesters.create', compact('courses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'course_id' => 'required',
            'name' => 'required',
            'year' => 'required|integer'
        ]);

        Semester::create($request->all());

        return redirect()->route('admin.semesters.index')
            ->with('success', 'Semester created successfully.');
    }

    public function destroy(Semester $semester)
    {
        $semester->delete();

        return redirect()->route('admin.semesters.index')
            ->with('success', 'Semester deleted successfully.');
    }
}
