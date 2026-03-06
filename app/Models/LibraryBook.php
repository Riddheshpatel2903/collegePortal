<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LibraryBook extends Model
{
    protected $fillable = [
        'title',
        'author',
        'publisher',
        'isbn',
        'category',
        'published_year',
        'quantity',
        'available_copies',
        'shelf_location',
        'status',
        'cover_path',
        'description',
    ];
}
