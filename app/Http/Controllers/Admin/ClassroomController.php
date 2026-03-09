<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\Course;
use App\Models\Subject;
use Illuminate\Http\Request;

class ClassroomController extends Controller
{
    public function index()
    {
        $classrooms = Classroom::with('course')->orderBy('name')->get();
        $activeCourses = Course::where('is_active', true)->get();

        $batches = [];
        foreach ($activeCourses as $course) {
            for ($year = 1; $year <= $course->duration_years; $year++) {
                $oddSem = ($year * 2) - 1;
                $evenSem = $year * 2;

                // Calculate required hours (taking max of odd/even to ensure room sufficiency)
                $oddLecture = Subject::where('course_id', $course->id)->where('semester_number', $oddSem)->where('is_lab', false)->sum('hours_per_week');
                $evenLecture = Subject::where('course_id', $course->id)->where('semester_number', $evenSem)->where('is_lab', false)->sum('hours_per_week');

                $oddLab = Subject::where('course_id', $course->id)->where('semester_number', $oddSem)->where('is_lab', true)->sum('hours_per_week');
                $evenLab = Subject::where('course_id', $course->id)->where('semester_number', $evenSem)->where('is_lab', true)->sum('hours_per_week');

                $assignedRooms = $classrooms->where('course_id', $course->id)
                    ->where('year_number', $year);

                $batches[] = [
                    'course_id' => $course->id,
                    'course_name' => $course->name,
                    'year' => $year,
                    'lecture_hours' => max($oddLecture, $evenLecture),
                    'lab_hours' => max($oddLab, $evenLab),
                    'assigned_rooms' => $assignedRooms
                ];
            }
        }

        return view('admin.classrooms.index', compact('classrooms', 'batches', 'activeCourses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:classrooms,name',
            'type' => 'required|in:lecture,lab',
            'capacity' => 'required|integer|min:1',
        ]);

        Classroom::create($validated);

        return back()->with('success', 'Classroom created successfully.');
    }

    public function update(Request $request, Classroom $classroom)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:classrooms,name,' . $classroom->id,
            'type' => 'required|in:lecture,lab',
            'capacity' => 'required|integer|min:1',
        ]);

        $classroom->update($validated);

        return back()->with('success', 'Classroom updated successfully.');
    }

    public function destroy(Classroom $classroom)
    {
        $classroom->delete();
        return back()->with('success', 'Classroom deleted successfully.');
    }

    public function assign(Request $request)
    {
        $validated = $request->validate([
            'classroom_id' => 'required|exists:classrooms,id',
            'course_id' => 'required|exists:courses,id',
            'year_number' => 'required|integer|min:1',
        ]);

        $room = Classroom::findOrFail($validated['classroom_id']);

        // If it's a lecture room, we typically only have one per batch.
        // If the user assigns a new lecture room, we clear the previous one for that batch.
        if ($room->type === 'lecture') {
            Classroom::where('course_id', $validated['course_id'])
                ->where('year_number', $validated['year_number'])
                ->where('type', 'lecture')
                ->where('id', '!=', $room->id)
                ->update(['course_id' => null, 'year_number' => null]);
        }

        $room->update([
            'course_id' => $validated['course_id'],
            'year_number' => $validated['year_number'],
        ]);

        return back()->with('success', 'Room assigned to ' . Course::find($validated['course_id'])->name . ' Year ' . $validated['year_number']);
    }

    public function unassign(Classroom $classroom)
    {
        $classroom->update([
            'course_id' => null,
            'year_number' => null,
        ]);

        return back()->with('success', 'Room unassigned successfully.');
    }
}
