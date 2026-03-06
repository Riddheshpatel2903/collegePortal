<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Teacher extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'department_id',
        'max_lectures_per_day',
        'qualification',
        'phone',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function subjects()
    {
        return $this->belongsToMany(SemesterSubject::class, 'teacher_subject_assignments', 'teacher_id', 'semester_subject_id')
            ->wherePivot('is_active', true);
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }

    public function subjectAssignments()
    {
        return $this->hasMany(TeacherSubjectAssignment::class);
    }
    public function approvedLeaves()
    {
        return $this->hasMany(Leave::class, 'approved_by');
    }

    public function leaves()
    {
        return $this->morphMany(Leave::class, 'leaveable');
    }

    public function availabilities()
    {
        return $this->hasMany(TeacherAvailability::class);
    }
}
