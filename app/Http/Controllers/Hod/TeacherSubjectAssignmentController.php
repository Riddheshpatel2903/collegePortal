<?php

namespace App\Http\Controllers\Hod;

use App\Http\Controllers\Controller;
use App\Http\Requests\Hod\UpsertTeacherAssignmentsRequest;
use App\Models\Course;
use App\Models\Department;
use App\Models\Teacher;
use App\Models\TeacherSubjectAssignment;
use App\Services\TeacherAssignmentService;
use Illuminate\Http\Request;

class TeacherSubjectAssignmentController extends Controller
{
    public function __construct(private TeacherAssignmentService $assignmentService) {}

    public function index(Request $request)
    {
        $department = Department::query()->where('hod_id', auth()->id())->firstOrFail();
        $courses = Course::query()
            ->where('department_id', $department->id)
            ->orderBy('name')
            ->get();

        $selectedCourseId = (int) ($request->integer('course_id') ?: $courses->first()?->id);
        $selectedCourse = $selectedCourseId ? $courses->firstWhere('id', $selectedCourseId) : null;
        $selectedYear = max(1, (int) ($request->integer('academic_year') ?: 1));

        $teachers = Teacher::query()
            ->where('department_id', $department->id)
            ->with('user:id,name,email')
            ->orderBy('id')
            ->get();

        $subjects = collect();
        $assignmentMap = collect();
        $semesterCounts = collect();
        if ($selectedCourse) {
            $subjects = $this->assignmentService->subjectsForCourseYear($selectedCourse, $selectedYear);
            $semesterCounts = $this->assignmentService->semesterCountsForCourseYear($selectedCourse, $selectedYear);
            $assignmentMap = TeacherSubjectAssignment::query()
                ->whereIn('subject_id', $subjects->pluck('id'))
                ->with('teacher.user:id,name')
                ->get()
                ->keyBy('subject_id');
        }

        return view('hod.teacher-assignments.index', [
            'department' => $department,
            'courses' => $courses,
            'teachers' => $teachers,
            'subjects' => $subjects,
            'assignmentMap' => $assignmentMap,
            'semesterCounts' => $semesterCounts,
            'selectedCourseId' => $selectedCourseId,
            'selectedYear' => $selectedYear,
        ]);
    }

    public function store(UpsertTeacherAssignmentsRequest $request)
    {
        $department = Department::query()->where('hod_id', auth()->id())->firstOrFail();
        $validated = $request->validated();

        $course = Course::query()
            ->where('id', (int) $validated['course_id'])
            ->where('department_id', $department->id)
            ->firstOrFail();

        $teachers = Teacher::query()
            ->where('department_id', $department->id)
            ->get(['id', 'department_id']);

        $this->assignmentService->saveAssignments(
            $course,
            (int) $validated['academic_year'],
            $validated['subject_teacher_map'],
            $teachers
        );

        return redirect()
            ->route('hod.teacher-assignments.index', [
                'course_id' => $course->id,
                'academic_year' => (int) $validated['academic_year'],
            ])
            ->with('success', 'Teacher assignments updated successfully.');
    }
}
