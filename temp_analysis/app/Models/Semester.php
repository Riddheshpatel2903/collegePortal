<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Semester extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'academic_session_id',
        'semester_number',
        'name',
        'start_date',
        'end_date',
        'is_current',
        'status'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_current' => 'boolean',
    ];

    // Relationships
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function academicSession()
    {
        return $this->belongsTo(AcademicSession::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'current_semester_id');
    }

    public function semesterSubjects()
    {
        return $this->hasMany(SemesterSubject::class);
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'semester_subjects')
                    ->withPivot(['credits', 'subject_type', 'is_mandatory', 'total_classes'])
                    ->withTimestamps();
    }

    public function studentFees()
    {
        return $this->hasMany(StudentFee::class);
    }

    public function results()
    {
        return $this->hasMany(Result::class);
    }

    public function teacherAssignments()
    {
        return $this->hasMany(TeacherSubjectAssignment::class);
    }

    // Scopes
    public function scopeCurrent($query)
    {
        return $query->where('is_current', true);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForCourse($query, $courseId)
    {
        return $query->where('course_id', $courseId);
    }
}
