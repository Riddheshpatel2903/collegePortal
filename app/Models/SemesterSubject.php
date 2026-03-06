<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SemesterSubject extends Model
{
    use HasFactory;

    protected $fillable = [
        'semester_id',
        'subject_id',
        'credits',
        'subject_type',
        'is_mandatory',
        'total_classes'
    ];

    protected $casts = [
        'is_mandatory' => 'boolean',
    ];

    // Relationships
    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacherAssignments()
    {
        return $this->hasMany(TeacherSubjectAssignment::class);
    }

    public function attendanceSessions()
    {
        return $this->hasMany(AttendanceSession::class);
    }

    public function resultSubjects()
    {
        return $this->hasMany(ResultSubject::class);
    }

    // Get assigned teacher
    public function assignedTeacher()
    {
        return $this->hasOneThrough(
            Teacher::class,
            TeacherSubjectAssignment::class,
            'semester_subject_id',
            'id',
            'id',
            'teacher_id'
        )->where('teacher_subject_assignments.is_active', true);
    }
}
