<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LibraryReservation extends Model
{
    protected $fillable = [
        'library_book_id',
        'student_id',
        'reserved_at',
        'status',
        'queue_position',
    ];

    protected $casts = [
        'reserved_at' => 'datetime',
    ];
}
