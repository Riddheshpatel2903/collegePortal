<?php

namespace App\Http\Controllers\Librarian;

use App\Http\Controllers\Controller;
use App\Models\LibraryBook;
use App\Models\LibraryIssue;
use App\Models\LibraryReservation;
use App\Models\LibraryRequest;
use App\Models\LibraryFine;

class DashboardController extends Controller
{
    public function index()
    {
        $totalBooks = LibraryBook::query()->sum('quantity');
        $booksIssued = LibraryIssue::query()->whereIn('status', ['issued', 'overdue'])->count();
        $booksAvailable = LibraryBook::query()->sum('available_copies');
        $overdueBooks = LibraryIssue::query()->where('status', 'overdue')->count();
        $reservedBooks = LibraryReservation::query()->where('status', 'active')->count();
        $activeBorrowers = LibraryIssue::query()->whereIn('status', ['issued', 'overdue'])->distinct('student_id')->count('student_id');

        return view('librarian.dashboard', compact(
            'totalBooks',
            'booksIssued',
            'booksAvailable',
            'overdueBooks',
            'reservedBooks',
            'activeBorrowers'
        ));
    }
}
