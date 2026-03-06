<?php

namespace App\Http\Controllers\Librarian;

use App\Http\Controllers\Controller;
use App\Models\LibraryIssue;

class HistoryController extends Controller
{
    public function index()
    {
        $history = LibraryIssue::query()->latest()->paginate(15);
        return view('librarian.history.index', compact('history'));
    }
}
