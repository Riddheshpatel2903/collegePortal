<?php

namespace App\Http\Controllers\Librarian;

use App\Http\Controllers\Controller;

class ReportsController extends Controller
{
    public function index()
    {
        return view('librarian.reports.index');
    }
}
