<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LibraryIssue extends Model
{
    protected $fillable = [
        'library_book_id',
        'student_id',
        'issued_by',
        'issue_date',
        'due_date',
        'return_date',
        'copies',
        'status',
        'fine_amount',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'return_date' => 'date',
    ];
}
