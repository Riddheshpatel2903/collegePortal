<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Attendance;
use App\Models\Course;
use App\Models\Event;
use App\Models\Notice;
use App\Models\Student;
use App\Models\Subject;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $student = Student::with(['user', 'course', 'fees', 'results'])->where('user_id', $user->id)->first();

        if (! $student) {
            abort(403, 'Student profile not found.');
        }

        $course = Course::find($student->course_id);
        $semestersPerYear = max(1, (int) ($course?->semesters_per_year ?? 2));
        $fromSemester = (($student->current_year - 1) * $semestersPerYear) + 1;
        $toSemester = $student->current_year * $semestersPerYear;

        $notices = Notice::with('user:id,name')
            ->where('is_active', true)
            ->whereIn('target_role', ['all', 'student'])
            ->latest()
            ->take(5)
            ->get();

        $events = Event::query()
            ->whereDate('event_date', '>=', now()->toDateString())
            ->orderBy('event_date')
            ->take(5)
            ->get();

        $attendanceQuery = Attendance::query()
            ->where('student_id', $student->id)
            ->whereHas('attendanceSession', function ($query) use ($student) {
                $query->where('course_id', $student->course_id)
                    ->where('academic_year', $student->current_year);
            });

        $totalAttendance = (clone $attendanceQuery)->count();
        $presentCount = (clone $attendanceQuery)->where('status', 'present')->count();
        $attendancePercent = $totalAttendance > 0 ? round(($presentCount / $totalAttendance) * 100) : 0;

        $pendingFees = $student->fees->where('status', '!=', 'paid')->sum('pending_amount');

        $subjectCount = Subject::query()
            ->where('course_id', $student->course_id)
            ->whereBetween('semester_sequence', [$fromSemester, $toSemester])
            ->count();

        $assignmentsDue = Assignment::query()
            ->where('course_id', $student->course_id)
            ->whereBetween('semester_number', [$fromSemester, $toSemester])
            ->where('status', 'published')
            ->where('is_active', true)
            ->whereDate('due_date', '>=', now()->toDateString())
            ->count();

        return view('student.dashboard', compact(
            'user',
            'student',
            'notices',
            'events',
            'attendancePercent',
            'pendingFees',
            'subjectCount',
            'assignmentsDue'
        ));
    }
}
