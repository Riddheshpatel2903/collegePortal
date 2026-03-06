<?php
// app/Http/Controllers/ResultController.php

namespace App\Http\Controllers;

use App\Models\Result;
use App\Models\Student;
use App\Models\Semester;
use App\Services\ResultService;
use Illuminate\Http\Request;

class ResultController extends Controller
{
    protected $resultService;

    public function __construct(ResultService $resultService)
    {
        $this->resultService = $resultService;
    }

    /**
     * Show result entry form
     */
    public function create()
    {
        $semesters = Semester::with('course')->where('status', 'active')->get();

        return view('results.create', compact('semesters'));
    }

    /**
     * Get students for result entry
     */
    public function getStudents(Request $request)
    {
        $semesterId = $request->semester_id;
        
        $semester = Semester::with('semesterSubjects.subject')->findOrFail($semesterId);
        
        $students = Student::where('current_semester_id', $semesterId)
            ->where('student_status', 'active')
            ->orderBy('roll_number')
            ->get();

        return response()->json([
            'students' => $students,
            'subjects' => $semester->semesterSubjects
        ]);
    }

    /**
     * Store result
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'semester_id' => 'required|exists:semesters,id',
            'marks' => 'required|array'
        ]);

        $student = Student::findOrFail($validated['student_id']);
        $semester = Semester::findOrFail($validated['semester_id']);

        $result = $this->resultService->submitResult($student, $semester, $validated['marks']);

        return redirect()->route('results.show', $result)
            ->with('success', 'Result submitted successfully. CGPA calculated and student promotion processed.');
    }

    /**
     * Display results
     */
    public function index(Request $request)
    {
        $query = Result::with(['student', 'semester']);

        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        if ($request->filled('semester_id')) {
            $query->where('semester_id', $request->semester_id);
        }

        if ($request->filled('status')) {
            $query->where('result_status', $request->status);
        }

        $results = $query->latest()->paginate(15);

        return view('results.index', compact('results'));
    }

    /**
     * Show result details
     */
    public function show(Result $result)
    {
        $result->load([
            'student',
            'semester',
            'resultSubjects.semesterSubject.subject'
        ]);

        return view('results.show', compact('result'));
    }

    /**
     * Student result card
     */
    public function studentResult()
    {
        $student = auth()->user()->student;

        if (!$student) {
            abort(403, 'Student profile not found.');
        }

        $results = $student->results()
            ->with(['semester', 'resultSubjects.semesterSubject.subject'])
            ->latest()
            ->get();

        return view('results.student-result', compact('results', 'student'));
    }
}
