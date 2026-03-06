<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LibraryRequest extends Model
{
    protected $fillable = [
        'student_id',
        'title',
        'author',
        'reason',
        'status',
    ];
}
