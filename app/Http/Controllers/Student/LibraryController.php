<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\LibraryBook;
use App\Models\LibraryIssue;
use App\Models\LibraryReservation;
use App\Models\LibraryRequest;
use App\Models\LibraryFine;

class LibraryController extends Controller
{
    public function dashboard()
    {
        $studentId = auth()->user()?->student?->id;
        $borrowed = LibraryIssue::query()->where('student_id', $studentId)->whereIn('status', ['issued', 'overdue'])->count();
        $dueSoon = LibraryIssue::query()->where('student_id', $studentId)->where('status', 'issued')->whereDate('due_date', '<=', now()->addDays(3))->count();
        $overdue = LibraryIssue::query()->where('student_id', $studentId)->where('status', 'overdue')->count();
        $reserved = LibraryReservation::query()->where('student_id', $studentId)->where('status', 'active')->count();

        return view('student.library.dashboard', compact('borrowed', 'dueSoon', 'overdue', 'reserved'));
    }

    public function browse()
    {
        $books = LibraryBook::query()->latest()->paginate(12);
        return view('student.library.browse', compact('books'));
    }

    public function borrowed()
    {
        $issues = LibraryIssue::query()->where('student_id', auth()->user()?->student?->id)->latest()->paginate(15);
        return view('student.library.borrowed', compact('issues'));
    }

    public function reservations()
    {
        $reservations = LibraryReservation::query()->where('student_id', auth()->user()?->student?->id)->latest()->paginate(15);
        return view('student.library.reservations', compact('reservations'));
    }

    public function requests()
    {
        $requests = LibraryRequest::query()->where('student_id', auth()->user()?->student?->id)->latest()->paginate(15);
        return view('student.library.requests', compact('requests'));
    }

    public function fines()
    {
        $fines = LibraryFine::query()->where('student_id', auth()->user()?->student?->id)->latest()->paginate(15);
        return view('student.library.fines', compact('fines'));
    }
}
