<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'course_id',
        'semester_number',
        'semester_sequence',
        'type',
        'hours_per_week',
        'teacher_id',
        'lab_duration',
        'credits',
        'weekly_hours',
        'is_lab',
        'lab_block_hours',
    ];

    protected $casts = [
        'is_lab' => 'boolean',
    ];
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function attendanceSessions()
    {
        return $this->hasManyThrough(AttendanceSession::class, SemesterSubject::class, 'subject_id', 'semester_subject_id');
    }

    public function semesterSubjects()
    {
        return $this->hasMany(SemesterSubject::class);
    }

    public function teacherAssignments()
    {
        return $this->hasMany(TeacherSubjectAssignment::class);
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }
    public function resultSubjects()
    {
        return $this->hasMany(ResultSubject::class);
    }
}
