<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'semester_subject_id',
        'teacher_id',
        'course_id',
        'subject_id',
        'academic_year',
        'semester_number',
        'date',
        'start_time',
        'end_time',
        'session_type',
        'topic',
        'is_completed',
    ];

    protected $casts = [
        'date' => 'date',
        'is_completed' => 'boolean',
    ];

    // Relationships
    public function semesterSubject()
    {
        return $this->belongsTo(SemesterSubject::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    // Get present count
    public function getPresentCountAttribute()
    {
        return $this->attendances()->where('status', 'present')->count();
    }

    // Get absent count
    public function getAbsentCountAttribute()
    {
        return $this->attendances()->where('status', 'absent')->count();
    }
}
