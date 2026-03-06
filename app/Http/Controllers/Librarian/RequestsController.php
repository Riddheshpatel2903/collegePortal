<?php

namespace App\Http\Controllers\Librarian;

use App\Http\Controllers\Controller;
use App\Models\LibraryRequest;

class RequestsController extends Controller
{
    public function index()
    {
        $requests = LibraryRequest::query()->latest()->paginate(15);
        return view('librarian.requests.index', compact('requests'));
    }
}
