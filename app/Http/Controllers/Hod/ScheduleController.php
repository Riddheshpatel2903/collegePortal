<?php

namespace App\Http\Controllers\Hod;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Department;
use App\Models\Teacher;
use App\Models\TeacherAvailability;
use App\Services\PortalAccessService;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function __construct(
        private \App\Services\Timetable\AutoTimetableService $service,
        private PortalAccessService $accessService,
    ) {}

    public function index(Request $request)
    {
        $department = Department::query()->where('hod_id', auth()->id())->firstOrFail();
        $courses = Course::query()->where('department_id', $department->id)->orderBy('name')->get();

        $courseId = $request->integer('course_id') ?: $courses->first()?->id;
        $semesterType = strtolower((string) $request->get('semester_type', 'odd'));

        $context = null;
        $gridData = null;
        if ($courseId > 0 && in_array($semesterType, ['odd', 'even'], true)) {
            $context = $this->service->generationContext($courseId, $semesterType);
            $gridData = $this->service->editableGrid($courseId, $semesterType);
        }

        return view('hod.timetable.index', [
            'department' => $department,
            'courses' => $courses,
            'selectedCourseId' => $courseId > 0 ? $courseId : null,
            'selectedSemesterType' => $semesterType,
            'context' => $context,
            'gridData' => $gridData,
        ]);
    }

    public function generate(Request $request)
    {
        // Increase execution time for long-running timetable generation
        ini_set('max_execution_time', '600'); // 10 minutes
        ini_set('memory_limit', '1024M');

        $validated = $request->validate([
            'course_id' => 'required|integer|exists:courses,id',
            'semester_type' => 'required|string|in:odd,even',
            'selected_years' => 'required|array',
            'selected_teacher_ids' => 'nullable|array',
            'selected_classroom_ids' => 'nullable|array',
        ]);

        try {
            $result = $this->service->generate($validated);
        } catch (\Throwable $exception) {
            return back()->withErrors(['generator' => $exception->getMessage()])->withInput();
        }

        return redirect()
            ->route('hod.timetable.index', [
                'course_id' => $result['course_id'],
                'semester_type' => $result['semester_type'],
            ])
            ->with('success', "Generated {$result['generated_count']} timetable slots successfully.");
    }

    public function updateEntry(Request $request, \App\Models\Timetable $entry)
    {
        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:teachers,id',
            'classroom_id' => 'required|exists:classrooms,id',
            'day' => 'required|string',
            'slot_number' => 'required|integer',
        ]);

        try {
            $this->service->updateSlot($entry, $validated);
        } catch (\Throwable $exception) {
            return back()->withErrors(['entry_'.$entry->id => $exception->getMessage()])->withInput();
        }

        return back()->with('success', 'Timetable slot updated successfully.');
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
            'start_time' => $validated['start_time'].':00',
            'end_time' => $validated['end_time'].':00',
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
}
