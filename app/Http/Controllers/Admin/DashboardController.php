<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Course;
use App\Models\Payment;
use App\Models\Notice;
use App\Models\Event;

class DashboardController extends Controller
{
    public function index()
    {
        $studentCount = Student::count();
        $teacherCount = Teacher::count();
        $courseCount = Course::count();
        $feeTotal = Payment::sum('amount');

        $notices = Notice::latest()->take(5)->get();
        // Since Event model might not exist or be different, let's use Notice with high priority for now as a fallback
        // OR check if Event model exists. It does exist in Models dir.
        $events = Event::where('event_date', '>=', now())
            ->orderBy('event_date')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'studentCount',
            'teacherCount',
            'courseCount',
            'feeTotal',
            'notices',
            'events'
        ));
    }
}
