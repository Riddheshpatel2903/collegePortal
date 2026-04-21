<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\Course;
use App\Models\Semester;
use App\Services\SemesterCalculationService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SemesterController extends Controller
{
    public function __construct(private SemesterCalculationService $semesterCalculationService) {}

    public function index()
    {
        $semesters = Semester::with(['course', 'academicSession'])
            ->orderByDesc('academic_session_id')
            ->orderBy('semester_number')
            ->paginate(20);

        return view('admin.semesters.index', compact('semesters'));
    }

    public function create()
    {
        $courses = Course::orderBy('name')->get();
        $sessions = AcademicSession::orderByDesc('start_year')->get();
        $currentSessionId = AcademicSession::where('is_current', true)->value('id');

        return view('admin.semesters.create', compact('courses', 'sessions', 'currentSessionId'));
    }

    public function store(Request $request)
    {
        $baseRules = [
            'course_id' => ['required', 'exists:courses,id'],
            'academic_session_id' => ['required', 'exists:academic_sessions,id'],
            'semester_number' => ['required', 'integer', 'min:1'],
            'name' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'status' => ['required', Rule::in(['upcoming', 'active', 'completed'])],
            'is_current' => ['nullable', 'boolean'],
        ];

        $course = Course::findOrFail($request->input('course_id'));
        $maxSemester = $this->semesterCalculationService->totalSemesters($course);
        $baseRules['semester_number'][] = 'max:'.$maxSemester;
        $baseRules['semester_number'][] = Rule::unique('semesters', 'semester_number')
            ->where(fn ($q) => $q
                ->where('course_id', $request->input('course_id'))
                ->where('academic_session_id', $request->input('academic_session_id')));

        $validated = $request->validate($baseRules);

        if (! empty($validated['is_current'])) {
            Semester::query()
                ->where('course_id', $validated['course_id'])
                ->where('academic_session_id', $validated['academic_session_id'])
                ->update(['is_current' => false]);
        }

        Semester::create([
            'course_id' => $validated['course_id'],
            'academic_session_id' => $validated['academic_session_id'],
            'semester_number' => $validated['semester_number'],
            'name' => $validated['name'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'is_current' => (bool) ($validated['is_current'] ?? false),
            'status' => $validated['status'],
        ]);

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
