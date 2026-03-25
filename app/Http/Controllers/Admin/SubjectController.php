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
        $subjects = Subject::with(['course', 'teacherAssignments.teacher.user'])->paginate(20);
        $subjects->getCollection()->transform(function ($subject) {
            $assignment = $subject->teacherAssignments->first();
            $subject->setRelation('teacher', $assignment?->teacher);
            return $subject;
        });

        $courses = Course::all();
        return view('admin.subjects.index', compact('subjects', 'courses'));
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

    public function import(Request $request, \App\Services\Timetable\CurriculumParser $parser)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'file' => 'required|file|mimes:pdf,csv,txt',
            'type' => 'required|in:pdf,csv',
        ]);

        $courseId = $request->integer('course_id');
        
        try {
            $semesters = $request->type === 'pdf' 
                ? $parser->parseGtuPdf($request->file('file'))
                : $parser->parseCsv($request->file('file'));

            if (empty($semesters)) {
                return back()->with('error', 'No subject data could be extracted.');
            }

            $count = 0;
            DB::transaction(function () use ($semesters, $courseId, &$count) {
                foreach ($semesters as $semData) {
                    $semesterNumber = (int) $semData['semester'];
                    
                    foreach ($semData['subjects'] as $s) {
                        // Find teacher if provided (CSV only for now)
                        $teacherIdFound = null;
                        $teacherName = $s['teacher'] ?? null;
                        if (!empty($teacherName)) {
                            $teacher = Teacher::whereHas('user', function ($q) use ($teacherName) {
                                $q->where('name', 'like', '%' . $teacherName . '%');
                            })->first();
                            if ($teacher) {
                                $teacherIdFound = $teacher->id;
                            }
                        }

                        $subject = Subject::updateOrCreate(
                            [
                                'course_id' => $courseId,
                                'code' => $s['subject_code'],
                            ],
                            [
                                'name' => $s['subject_name'],
                                'semester_number' => $semesterNumber,
                                'semester_sequence' => $semesterNumber,
                                'lecture_hours' => (int)$s['lecture_hours'],
                                'tutorial_hours' => (int)$s['tutorial_hours'],
                                'practical_hours' => (int)$s['practical_hours'],
                                'credits' => (int)$s['credits'],
                                'internal_marks' => (int)$s['internal_marks'],
                                'external_marks' => (int)$s['external_marks'],
                                'total_marks' => (int)$s['total_marks'],
                                'type' => $s['subject_type'],
                                'weekly_hours' => (int)$s['lecture_hours'] + (int)$s['tutorial_hours'] + (int)$s['practical_hours'],
                                'is_lab' => (int)$s['practical_hours'] > 0,
                                'teacher_id' => $teacherIdFound,
                            ]
                        );
                        
                        // Link to semester if exists
                        $semester = Semester::where('course_id', $courseId)
                            ->where('semester_number', $semesterNumber)
                            ->first();
                            
                        $semesterSubjectId = null;
                        if ($semester) {
                            $ss = SemesterSubject::updateOrCreate(
                                ['semester_id' => $semester->id, 'subject_id' => $subject->id],
                                ['credits' => (int)$s['credits'], 'subject_type' => 'core', 'is_mandatory' => true]
                            );
                            $semesterSubjectId = $ss->id;
                        }

                        // Create assignment if teacher found
                        if ($teacherIdFound) {
                            TeacherSubjectAssignment::updateOrCreate(
                                ['subject_id' => $subject->id],
                                [
                                    'teacher_id' => $teacherIdFound,
                                    'semester_id' => $semester?->id,
                                    'semester_subject_id' => $semesterSubjectId,
                                    'academic_session_id' => $semester?->academic_session_id,
                                    'assigned_date' => now(),
                                    'is_active' => true,
                                ]
                            );
                        }
                        $count++;
                    }
                }
            });

            return back()->with('success', "Curriculum imported successfully. Added/Updated {$count} subjects.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
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

    public function deleteAll()
    {
        try {
            DB::transaction(function () {
                // Clear related data first to avoid FK constraints
                TeacherSubjectAssignment::query()->delete();
                SemesterSubject::query()->delete();
                Subject::query()->delete();
            });
            return back()->with('success', 'All subjects and assignments have been deleted.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Failed to delete subjects: ' . $e->getMessage());
        }
    }
}
