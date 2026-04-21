<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Notice;
use App\Models\Schedule;
use App\Models\Teacher;
use App\Models\TeacherSubjectAssignment;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $teacher = Teacher::with('department')->where('user_id', $user->id)->first();
        if (! $teacher) {
            abort(403, 'Teacher profile not found.');
        }

        $assignments = Assignment::query()
            ->where('teacher_id', $teacher->id)
            ->latest()
            ->take(5)
            ->get();

        $subjects = TeacherSubjectAssignment::query()
            ->with('subject.course')
            ->where('teacher_id', $teacher->id)
            ->where('is_active', true)
            ->get()
            ->pluck('subject')
            ->filter();

        $todayClasses = Schedule::query()
            ->where('teacher_id', $teacher->id)
            ->where('day_of_week', strtolower(now()->format('l')))
            ->count();

        $notices = Notice::with('user:id,name')
            ->whereIn('target_role', ['all', 'teacher'])
            ->where('is_active', true)
            ->latest()
            ->take(5)
            ->get();

        return view('teacher.dashboard', compact(
            'user',
            'teacher',
            'subjects',
            'assignments',
            'notices',
            'todayClasses'
        ));
    }
}
