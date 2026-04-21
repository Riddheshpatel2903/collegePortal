<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentLeave extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'approved_by',
        'leave_type',
        'reason',
        'start_date',
        'end_date',
        'total_days',
        'status',
        'faculty_remark',
        'applied_at',
        'reviewed_at',
    ];

    protected $dates = [
        'start_date',
        'end_date',
        'applied_at',
        'reviewed_at',
    ];

    // 🔹 Relationship with Student
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    // 🔹 Relationship with Teacher (Approver)
    public function approver()
    {
        return $this->belongsTo(Teacher::class, 'approved_by');
    }
}
