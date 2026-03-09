<?php

namespace App\Http\Controllers\Hod;

use App\Http\Controllers\Controller;
use App\Http\Requests\Hod\GenerateTimetableRequest;
use App\Http\Requests\Hod\UpdateTimetableSlotRequest;
use App\Models\Classroom;
use App\Models\Course;
use App\Models\Department;
use App\Models\Schedule;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherAvailability;
use App\Models\TeacherSubjectAssignment;
use App\Services\ScheduleService;
use App\Services\TimetableGeneratorService;
use App\Services\TeacherAssignmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use App\Services\PortalAccessService;
use Illuminate\Validation\ValidationException;

class ScheduleController extends Controller
{
    public function __construct(
        private TimetableGeneratorService $generatorService,
        private ScheduleService $scheduleService,
        private TeacherAssignmentService $assignmentService,
        private PortalAccessService $accessService,
    ) {
    }

    public function index(Request $request)
    {
        $department = Department::query()->where('hod_id', auth()->id())->firstOrFail();
        $courses = Course::query()->where('department_id', $department->id)->orderBy('name')->get();

        $selectedCourseId = (int) ($request->integer('course_id') ?: $courses->first()?->id);
        $selectedCourse = $selectedCourseId ? $courses->firstWhere('id', $selectedCourseId) : null;
        $selectedYear = max(1, (int) ($request->integer('academic_year') ?: 1));

        $subjects = $selectedCourse
            ? $this->assignmentService->subjectsForCourseYear($selectedCourse, $selectedYear)
            : collect();

        $assignmentMap = TeacherSubjectAssignment::query()
            ->whereIn('subject_id', $subjects->pluck('id'))
            ->with('teacher.user:id,name')
            ->get()
            ->keyBy('subject_id');

        $teachers = Teacher::query()
            ->where('department_id', $department->id)
            ->with('user:id,name,email')
            ->orderBy('id')
            ->get();

        $availabilities = TeacherAvailability::query()
            ->whereIn('teacher_id', $teachers->pluck('id'))
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get()
            ->groupBy('teacher_id');

        $schedules = $this->scheduleQueryForClass($selectedCourse, $selectedYear)->get();
        [$timeSlots, $grid] = $this->buildGrid($schedules);

        return view('hod.timetable.index', [
            'department' => $department,
            'courses' => $courses,
            'teachers' => $teachers,
            'subjects' => $subjects,
            'assignmentMap' => $assignmentMap,
            'availabilities' => $availabilities,
            'selectedCourseId' => $selectedCourseId,
            'selectedYear' => $selectedYear,
            'timeSlots' => $timeSlots,
            'days' => $this->accessService->workingDays(),
            'grid' => $grid,
            'listSchedules' => $schedules,
        ]);
    }

    public function generate(GenerateTimetableRequest $request)
    {
        // Increase execution time for long-running timetable generation
        ini_set('max_execution_time', '600'); // 10 minutes
        ini_set('memory_limit', '1024M');

        $department = Department::query()->where('hod_id', auth()->id())->firstOrFail();
        $validated = $request->validated();

        $course = Course::query()
            ->where('id', (int) $validated['course_id'])
            ->where('department_id', $department->id)
            ->firstOrFail();

        $subjects = $this->assignmentService->subjectsForCourseYear($course, (int) $validated['academic_year'])
            ->when(
                !empty($validated['subject_ids']),
                fn (Collection $rows) => $rows->whereIn('id', collect($validated['subject_ids'])->map(fn ($id) => (int) $id))
            );

        if ($subjects->isEmpty()) {
            return back()->withErrors(['generator' => 'No subjects found for selected class context.'])->withInput();
        }

        $assignedSubjectIds = TeacherSubjectAssignment::query()
            ->whereIn('subject_id', $subjects->pluck('id'))
            ->pluck('subject_id')
            ->map(fn ($id) => (int) $id);

        $unassigned = $subjects->reject(fn ($subject) => $assignedSubjectIds->contains((int) $subject->id));
        if ($unassigned->isNotEmpty()) {
            return back()->withErrors([
                'generator' => 'Assign teachers first for all selected subjects: ' . $unassigned->pluck('name')->implode(', '),
            ])->withInput();
        }

        try {
            $result = $this->generatorService->generate([
                'course_id' => $course->id,
                'academic_year' => (int) $validated['academic_year'],
                'subject_ids' => $subjects->pluck('id')->values()->all(),
                'clear_existing' => (bool) ($validated['clear_existing'] ?? false),
            ]);
        } catch (\Throwable $exception) {
            return back()->withErrors(['generator' => $exception->getMessage()])->withInput();
        }

        return redirect()
            ->route('hod.timetable.index', ['course_id' => $course->id, 'academic_year' => (int) $validated['academic_year']])
            ->with('success', "Generated {$result['generated_count']} timetable slots successfully.");
    }

    public function edit(Schedule $schedule)
    {
        $department = Department::query()->where('hod_id', auth()->id())->firstOrFail();
        $schedule->load(['subject.course', 'teacher.user', 'classroom', 'semester.course']);

        $this->assertScheduleInDepartment($schedule, $department->id);

        $course = $schedule->subject?->course;
        $teachers = Teacher::query()
            ->where('department_id', $department->id)
            ->with('user:id,name')
            ->orderBy('id')
            ->get();
        $subjects = Subject::query()
            ->where('course_id', $course?->id)
            ->orderBy('semester_sequence')
            ->orderBy('name')
            ->get();

        return view('hod.timetable.edit', [
            'schedule' => $schedule,
            'teachers' => $teachers,
            'subjects' => $subjects,
            'classrooms' => Classroom::query()->orderBy('name')->get(),
            'course' => $course,
            'year' => $this->resolveAcademicYear($schedule),
        ]);
    }

    public function update(UpdateTimetableSlotRequest $request, Schedule $schedule)
    {
        $department = Department::query()->where('hod_id', auth()->id())->firstOrFail();
        $schedule->loadMissing('subject.course');
        $this->assertScheduleInDepartment($schedule, $department->id);

        $validated = $request->validated();
        $subject = Subject::query()->with('course')->findOrFail((int) $validated['subject_id']);
        if ((int) $subject->course?->department_id !== (int) $department->id) {
            throw ValidationException::withMessages(['subject_id' => 'Selected subject is outside your department.']);
        }

        $teacher = Teacher::query()->findOrFail((int) $validated['teacher_id']);
        if ((int) $teacher->department_id !== (int) $department->id) {
            throw ValidationException::withMessages(['teacher_id' => 'Selected teacher is outside your department.']);
        }

        $semester = $this->resolveSemesterForSubject($subject);

        $this->scheduleService->update($schedule, [
            'semester_id' => $semester?->id ?? $schedule->semester_id,
            'subject_id' => (int) $validated['subject_id'],
            'teacher_id' => (int) $validated['teacher_id'],
            'classroom_id' => (int) $validated['classroom_id'],
            'day_of_week' => $validated['day_of_week'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
        ]);

        $courseId = $subject->course_id;
        $year = $this->subjectAcademicYear($subject);

        return redirect()
            ->route('hod.timetable.index', ['course_id' => $courseId, 'academic_year' => $year])
            ->with('success', 'Timetable slot updated successfully.');
    }

    public function setAvailability(Request $request)
    {
        $department = Department::query()->where('hod_id', auth()->id())->firstOrFail();

        $validated = $request->validate([
            'teacher_id' => ['required', 'integer', 'exists:teachers,id'],
            'day_of_week' => ['required', 'in:monday,tuesday,wednesday,thursday,friday,saturday'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
        ]);

        $teacher = Teacher::query()
            ->where('id', (int) $validated['teacher_id'])
            ->where('department_id', $department->id)
            ->firstOrFail();

        TeacherAvailability::create([
            'teacher_id' => $teacher->id,
            'day_of_week' => $validated['day_of_week'],
            'start_time' => $validated['start_time'] . ':00',
            'end_time' => $validated['end_time'] . ':00',
        ]);

        return back()->with('success', 'Teacher availability updated.');
    }

    public function deleteAvailability(TeacherAvailability $availability)
    {
        $departmentId = Department::query()->where('hod_id', auth()->id())->value('id');
        Teacher::query()
            ->where('id', $availability->teacher_id)
            ->where('department_id', (int) $departmentId)
            ->firstOrFail();

        $availability->delete();
        return back()->with('success', 'Availability removed successfully.');
    }

    private function scheduleQueryForClass(?Course $course, int $academicYear)
    {
        return Schedule::query()
            ->with(['subject.course', 'teacher.user', 'classroom', 'semester.course'])
            ->when($course, function ($query) use ($course, $academicYear) {
                $semestersPerYear = max(1, (int) ($course->semesters_per_year ?? 2));
                $from = (($academicYear - 1) * $semestersPerYear) + 1;
                $to = $academicYear * $semestersPerYear;
                $query->whereHas('subject', fn ($sq) => $sq->where('course_id', $course->id)->whereBetween('semester_sequence', [$from, $to]));
            })
            ->orderByRaw("FIELD(day_of_week, 'monday','tuesday','wednesday','thursday','friday','saturday')")
            ->orderBy('start_time');
    }

    private function assertScheduleInDepartment(Schedule $schedule, int $departmentId): void
    {
        abort_unless(
            (int) ($schedule->subject?->course?->department_id ?? 0) === (int) $departmentId,
            403,
            'Timetable slot is outside your department.'
        );
    }

    private function resolveSemesterForSubject(Subject $subject): ?Semester
    {
        return Semester::query()
            ->where('course_id', $subject->course_id)
            ->where('semester_number', (int) $subject->semester_sequence)
            ->orderByDesc('is_current')
            ->orderByDesc('id')
            ->first();
    }

    private function subjectAcademicYear(Subject $subject): int
    {
        $semestersPerYear = max(1, (int) ($subject->course?->semesters_per_year ?? 2));
        return (int) ceil((int) $subject->semester_sequence / $semestersPerYear);
    }

    private function resolveAcademicYear(Schedule $schedule): int
    {
        $semestersPerYear = max(1, (int) ($schedule->subject?->course?->semesters_per_year ?? 2));
        $semesterSeq = (int) ($schedule->subject?->semester_sequence ?? $schedule->semester?->semester_number ?? 1);
        return (int) ceil($semesterSeq / $semestersPerYear);
    }

    private function buildGrid(Collection $schedules): array
    {
        $timeSlots = collect(config('timetable.slot_blocks', []))
            ->map(fn ($slot) => "{$slot[0]}-{$slot[1]}")
            ->values();

        $days = $this->accessService->workingDays();
        $grid = [];
        foreach ($days as $day) {
            $grid[$day] = [];
            foreach ($timeSlots as $timeSlot) {
                $grid[$day][$timeSlot] = $schedules->first(
                    fn ($slot) => $slot->day_of_week === $day
                        && ($slot->start_time . '-' . $slot->end_time) === $timeSlot
                );
            }
        }

        return [$timeSlots, $grid];
    }
}
