<?php

namespace App\Http\Controllers\Librarian;

use App\Http\Controllers\Controller;
use App\Models\LibraryFine;

class FinesController extends Controller
{
    public function index()
    {
        $fines = LibraryFine::query()->latest()->paginate(15);

        return view('librarian.fines.index', compact('fines'));
    }
}
