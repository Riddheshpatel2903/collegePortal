<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GenerateAutoTimetableRequest;
use App\Http\Requests\Admin\UpdateTimetableEntryRequest;
use App\Models\Course;
use App\Models\Timetable;
use App\Services\AutoTimetableService;
use Illuminate\Http\Request;

class AutoTimetableController extends Controller
{
    public function __construct(private AutoTimetableService $service)
    {
    }

    public function index(Request $request)
    {
        $courses = Course::query()->with('department:id,name')->orderBy('name')->get();
        $courseId = $request->integer('course_id');
        $semesterType = strtolower((string) $request->get('semester_type', 'odd'));

        $context = null;
        $gridData = null;
        if ($courseId > 0 && in_array($semesterType, ['odd', 'even'], true)) {
            $context = $this->service->generationContext($courseId, $semesterType);
            $gridData = $this->service->editableGrid($courseId, $semesterType);
        }

        return view('admin.timetable-auto.index', [
            'courses' => $courses,
            'selectedCourseId' => $courseId > 0 ? $courseId : null,
            'selectedSemesterType' => $semesterType,
            'context' => $context,
            'gridData' => $gridData,
        ]);
    }

    public function generate(GenerateAutoTimetableRequest $request)
    {
        try {
            $result = $this->service->generate($request->validated());
        } catch (\Throwable $exception) {
            return back()->withErrors(['generator' => $exception->getMessage()])->withInput();
        }

        return redirect()
            ->route('admin.timetable-auto.index', [
                'course_id' => $result['course_id'],
                'semester_type' => $result['semester_type'],
            ])
            ->with('success', "Generated {$result['generated_count']} timetable slots.");
    }

    public function updateEntry(UpdateTimetableEntryRequest $request, Timetable $entry)
    {
        try {
            $this->service->updateSlot($entry, $request->validated());
        } catch (\Throwable $exception) {
            return back()->withErrors(['entry_' . $entry->id => $exception->getMessage()])->withInput();
        }

        return back()->with('success', 'Timetable slot updated successfully.');
    }
}

