<?php

namespace App\Http\Controllers\Librarian;

use App\Http\Controllers\Controller;
use App\Models\LibraryBook;

class BooksController extends Controller
{
    public function index()
    {
        $books = LibraryBook::query()->latest()->paginate(15);

        return view('librarian.books.index', compact('books'));
    }
}
