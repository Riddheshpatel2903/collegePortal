<?php

namespace App\Http\Controllers\Librarian;

use App\Http\Controllers\Controller;
use App\Models\LibraryIssue;

class OverduesController extends Controller
{
    public function index()
    {
        $overdues = LibraryIssue::query()->where('status', 'overdue')->latest()->paginate(15);

        return view('librarian.overdues.index', compact('overdues'));
    }
}
