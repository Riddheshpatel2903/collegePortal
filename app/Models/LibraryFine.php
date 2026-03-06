<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LibraryFine extends Model
{
    protected $fillable = [
        'library_issue_id',
        'student_id',
        'days_late',
        'amount',
        'status',
        'paid_at',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
    ];
}
