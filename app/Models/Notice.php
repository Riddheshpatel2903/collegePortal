<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notice extends Model
{
    protected $fillable = [
        'title',
        'content',
        'posted_by',
        'target_role',
        'department_id',
        'notice_for',
        'course_id',
        'priority',
        'is_active',
        'expiry_date',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'expiry_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
