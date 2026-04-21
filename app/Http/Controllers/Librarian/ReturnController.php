<?php

namespace App\Http\Controllers\Librarian;

use App\Http\Controllers\Controller;
use App\Models\LibraryIssue;

class ReturnController extends Controller
{
    public function index()
    {
        $issues = LibraryIssue::query()->latest()->paginate(15);

        return view('librarian.returns.index', compact('issues'));
    }
}
