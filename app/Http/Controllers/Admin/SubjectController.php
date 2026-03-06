<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Semester;
use App\Models\SemesterSubject;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherSubjectAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class SubjectController extends Controller
{
    public function index()
    {
        $subjects = Subject::with(['course', 'teacherAssignments.teacher.user'])->paginate(10);
        $subjects->getCollection()->transform(function ($subject) {
            $assignment = $subject->teacherAssignments->first();
            $subject->setRelation('teacher', $assignment?->teacher);
            return $subject;
        });

        return view('admin.subjects.index', compact('subjects'));
    }

    public function create()
    {
        return view('admin.subjects.create', [
            'courses' => Course::all(),
            'teachers' => Teacher::with('user')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'course_id' => 'required|exists:courses,id',
            'semester_number' => 'required|integer|min:1|max:20',
            'teacher_id' => 'required|exists:teachers,id',
            'credits' => 'required|integer|min:1|max:10',
            'weekly_hours' => 'required|integer|min:1|max:30',
            'is_lab' => 'nullable|boolean',
            'lab_block_hours' => 'nullable|integer|min:2|max:3',
        ]);

        DB::transaction(function () use ($request) {
            $semesterNumber = (int) $request->semester_number;
            $this->assertSemesterNumberWithinCourse((int) $request->course_id, $semesterNumber);
            $this->assertSubjectLimitPerSemester((int) $request->course_id, $semesterNumber);

            $payload = [
                'name' => $request->name,
                'course_id' => (int) $request->course_id,
                'semester_sequence' => $semesterNumber,
                'credits' => (int) $request->credits,
            ];
            if (Schema::hasColumn('subjects', 'weekly_hours')) {
                $payload['weekly_hours'] = (int) $request->weekly_hours;
            }
            if (Schema::hasColumn('subjects', 'is_lab')) {
                $payload['is_lab'] = $request->boolean('is_lab');
            }
            if (Schema::hasColumn('subjects', 'lab_block_hours')) {
                $payload['lab_block_hours'] = $request->boolean('is_lab') ? (int) ($request->lab_block_hours ?: 2) : null;
            }

            $subject = Subject::create($payload);

            $semester = Semester::query()
                ->where('course_id', (int) $request->course_id)
                ->where('semester_number', $semesterNumber)
                ->first();

            $semesterSubjectId = null;
            $academicSessionId = null;
            if ($semester) {
                $semesterSubject = SemesterSubject::updateOrCreate(
                    ['semester_id' => $semester->id, 'subject_id' => $subject->id],
                    ['credits' => $request->credits, 'subject_type' => 'core', 'is_mandatory' => true]
                );
                $semesterSubjectId = $semesterSubject->id;
                $academicSessionId = $semester->academic_session_id;
            }

            TeacherSubjectAssignment::updateOrCreate(
                ['subject_id' => $subject->id],
                [
                    'teacher_id' => (int) $request->teacher_id,
                    'semester_id' => $semester?->id,
                    'semester_subject_id' => $semesterSubjectId,
                    'academic_session_id' => $academicSessionId,
                    'assigned_date' => now(),
                    'is_active' => true,
                ]
            );
        });

        return redirect()->route('admin.subjects.index')->with('success', 'Subject created successfully.');
    }

    public function edit(Subject $subject)
    {
        $subject->load('teacherAssignments');
        $subject->semester_number = (int) $subject->semester_sequence;
        $subject->teacher_id = $subject->teacherAssignments->first()?->teacher_id;

        return view('admin.subjects.edit', [
            'subject' => $subject,
            'courses' => Course::all(),
            'teachers' => Teacher::with('user')->get(),
        ]);
    }

    public function update(Request $request, Subject $subject)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'credits' => 'required|integer|min:1|max:10',
            'course_id' => 'required|exists:courses,id',
            'semester_number' => 'required|integer|min:1|max:20',
            'teacher_id' => 'required|exists:teachers,id',
            'weekly_hours' => 'required|integer|min:1|max:30',
            'is_lab' => 'nullable|boolean',
            'lab_block_hours' => 'nullable|integer|min:2|max:3',
        ]);

        DB::transaction(function () use ($request, $subject) {
            $semesterNumber = (int) $request->semester_number;
            $this->assertSemesterNumberWithinCourse((int) $request->course_id, $semesterNumber);
            $this->assertSubjectLimitPerSemester((int) $request->course_id, $semesterNumber, (int) $subject->id);

            $payload = [
                'name' => $request->name,
                'credits' => (int) $request->credits,
                'course_id' => (int) $request->course_id,
                'semester_sequence' => $semesterNumber,
            ];
            if (Schema::hasColumn('subjects', 'weekly_hours')) {
                $payload['weekly_hours'] = (int) $request->weekly_hours;
            }
            if (Schema::hasColumn('subjects', 'is_lab')) {
                $payload['is_lab'] = $request->boolean('is_lab');
            }
            if (Schema::hasColumn('subjects', 'lab_block_hours')) {
                $payload['lab_block_hours'] = $request->boolean('is_lab') ? (int) ($request->lab_block_hours ?: 2) : null;
            }
            $subject->update($payload);

            $semester = Semester::query()
                ->where('course_id', (int) $request->course_id)
                ->where('semester_number', $semesterNumber)
                ->first();

            $semesterSubjectId = null;
            $academicSessionId = null;
            if ($semester) {
                $semesterSubject = SemesterSubject::updateOrCreate(
                    ['semester_id' => $semester->id, 'subject_id' => $subject->id],
                    ['credits' => $request->credits, 'subject_type' => 'core', 'is_mandatory' => true]
                );
                $semesterSubjectId = $semesterSubject->id;
                $academicSessionId = $semester->academic_session_id;
            }

            TeacherSubjectAssignment::updateOrCreate(
                ['subject_id' => $subject->id],
                [
                    'teacher_id' => (int) $request->teacher_id,
                    'semester_id' => $semester?->id,
                    'semester_subject_id' => $semesterSubjectId,
                    'academic_session_id' => $academicSessionId,
                    'assigned_date' => now(),
                    'is_active' => true,
                ]
            );
        });

        return redirect()->route('admin.subjects.index')->with('success', 'Subject updated successfully.');
    }

    public function destroy(Subject $subject)
    {
        $subject->delete();
        return redirect()->route('admin.subjects.index')->with('success', 'Subject deleted successfully.');
    }

    private function assertSemesterNumberWithinCourse(int $courseId, int $semesterNumber): void
    {
        $course = Course::findOrFail($courseId);
        $maxSemester = max(1, (int) $course->duration_years * max(1, (int) $course->semesters_per_year));

        if ($semesterNumber < 1 || $semesterNumber > $maxSemester) {
            throw ValidationException::withMessages([
                'semester_number' => "Semester must be between 1 and {$maxSemester} for {$course->name}.",
            ]);
        }
    }

    private function assertSubjectLimitPerSemester(int $courseId, int $semesterNumber, ?int $ignoreSubjectId = null): void
    {
        $required = max(1, (int) config('timetable.subjects_per_semester', 8));
        $count = Subject::query()
            ->where('course_id', $courseId)
            ->where('semester_sequence', $semesterNumber)
            ->when($ignoreSubjectId, fn ($q) => $q->whereKeyNot($ignoreSubjectId))
            ->count();

        if ($count >= $required) {
            throw ValidationException::withMessages([
                'semester_number' => "Semester {$semesterNumber} already has {$count} subjects. Maximum allowed is {$required}.",
            ]);
        }
    }
}
