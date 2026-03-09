<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Result;
use App\Models\ResultSubject;
use App\Models\Student;
use App\Models\TeacherSubjectAssignment;
use App\Services\ResultService;
use Illuminate\Http\Request;

class ResultController extends Controller
{
    public function __construct(private ResultService $resultService)
    {
    }

    public function index()
    {
        $teacher = auth()->user()->teacher;
        abort_if(!$teacher, 403, 'Teacher profile not found.');

        $assignments = TeacherSubjectAssignment::with(['subject.course'])
            ->where('teacher_id', $teacher->id)
            ->where('is_active', true)
            ->get();

        $semesterNumbers = $assignments->map(fn($a) => (int) ($a->subject?->semester_sequence ?? 0))
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $semesters = $semesterNumbers->map(fn($no) => (object) [
            'id' => $no,
            'name' => "Semester {$no}",
            'course' => (object) ['name' => 'Derived'],
        ])->values();

        $subjects = $assignments->map(fn($a) => (object) [
            'id' => $a->subject_id,
            'name' => $a->subject?->name ?? 'Subject',
            'semester_id' => (int) ($a->subject?->semester_sequence ?? 1),
        ])->values();

        return view('teacher.results.index', compact('subjects', 'semesters'));
    }

    public function load(Request $request)
    {
        $request->validate([
            'semester_id' => 'required|integer|min:1|max:20',
            'academic_year' => 'required|string',
        ]);

        $teacher = auth()->user()->teacher;
        abort_if(!$teacher, 403, 'Teacher profile not found.');
        $semesterId = (int) $request->semester_id;
        $academicYear = (string) $request->academic_year;

        $assignments = TeacherSubjectAssignment::with('subject.course')
            ->where('teacher_id', $teacher->id)
            ->whereHas('subject', fn($q) => $q->where('semester_sequence', $semesterId))
            ->where('is_active', true)
            ->get();

        if ($assignments->isEmpty()) {
            return response()->json([
                'subjects' => [],
                'students' => [],
                'marks' => [],
                'message' => 'No assigned subjects found for this semester.'
            ]);
        }

        $subjects = $assignments->map(fn($a) => [
            'id' => $a->subject_id,
            'name' => $a->subject?->name ?? 'Subject',
        ])->values();

        $studentsQuery = Student::with('user')->where(function ($outer) use ($assignments, $semesterId) {
            foreach ($assignments->pluck('subject.course')->filter()->unique('id') as $course) {
                $year = (int) ceil($semesterId / max(1, (int) ($course->semesters_per_year ?? 2)));
                $outer->orWhere(function ($q) use ($course, $year) {
                    $q->where('course_id', $course->id)->where('current_year', $year);
                });
            }
        });

        $students = $studentsQuery->get()
            ->map(fn($s) => [
                'id' => $s->id,
                'name' => $s->user?->name ?? 'N/A',
                'roll' => $s->roll_number ?? '',
            ])->values();

        $results = Result::with('resultSubjects')
            ->where('semester_number', $semesterId)
            ->whereIn('student_id', $students->pluck('id'))
            ->get()
            ->keyBy('student_id');

        $marksMap = [];
        foreach ($results as $studentId => $result) {
            $marksMap[$studentId] = [
                'result_id' => $result->id,
                'status' => $result->result_status === 'pending' ? 'draft' : 'published',
                'published_at' => $result->result_declared_date,
                'subjects' => [],
            ];

            foreach ($result->resultSubjects as $rs) {
                $marksMap[$studentId]['subjects'][$rs->subject_id] = [
                    'internal' => (int) $rs->internal_marks,
                    'final' => (int) $rs->external_marks,
                    'total' => (int) ($rs->internal_marks + $rs->external_marks),
                    'grade' => $rs->grade,
                    'status' => $rs->subject_status,
                ];
            }
        }

        return response()->json([
            'subjects' => $subjects,
            'students' => $students,
            'marks' => $marksMap,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'semester_id' => 'required|integer|min:1|max:20',
            'academic_year' => 'required|string',
            'marks' => 'required|array',
        ]);
        $semesterNumber = (int) $request->semester_id;

        foreach ($request->marks as $studentId => $subjects) {
            $student = Student::find($studentId);
            if (!$student) {
                continue;
            }

            $normalized = [];
            foreach ($subjects as $subjectId => $data) {
                $normalized[$subjectId] = [
                    'internal' => (int) ($data['internal'] ?? 0),
                    'external' => (int) ($data['final'] ?? 0),
                    'max_marks' => 100,
                ];
            }

            $this->resultService->submitResult($student, $semesterNumber, $normalized);
        }

        return response()->json(['success' => true]);
    }

    public function publish($id)
    {
        $result = Result::findOrFail($id);

        // Final recalculation of backlog subjects before publishing
        $backlogs = $result->resultSubjects()->where('subject_status', 'fail')->count();

        $result->update([
            'result_status' => $backlogs > 0 ? 'fail' : 'pass',
            'backlog_subjects' => $backlogs,
            'result_declared_date' => now(),
        ]);

        return response()->json(['success' => true, 'published_at' => $result->result_declared_date->format('Y-m-d H:i:s')]);
    }

    public function unlock($id)
    {
        $result = Result::findOrFail($id);
        $result->update([
            'result_status' => 'pending',
            'result_declared_date' => null,
        ]);

        return response()->json(['success' => true]);
    }

    public function search(Request $request)
    {
        $teacher = auth()->user()->teacher;
        abort_if(!$teacher, 403, 'Teacher profile not found.');
        $courseIds = TeacherSubjectAssignment::query()
            ->where('teacher_id', $teacher->id)
            ->where('is_active', true)
            ->whereHas('subject')
            ->with('subject:id,course_id')
            ->get()
            ->pluck('subject.course_id')
            ->filter()
            ->unique()
            ->values();

        $results = Result::query()
            ->with(['student.user', 'student.course'])
            ->whereIn('course_id', $courseIds)
            ->searchStudent((string) $request->input('search'))
            ->when($request->filled('semester_number'), fn($q) => $q->where('semester_number', (int) $request->semester_number))
            ->orderByDesc('semester_number')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('teacher.results.search', compact('results'));
    }
}
