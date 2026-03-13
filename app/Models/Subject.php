<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'course_id',
        'semester_number',
        'semester_sequence',
        'type',
        'lecture_hours',
        'tutorial_hours',
        'practical_hours',
        'hours_per_week',
        'teacher_id',
        'lab_duration',
        'credits',
        'weekly_hours',
        'is_lab',
        'lab_block_hours',
        'internal_marks',
        'external_marks',
        'total_marks',
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

    /**
     * Calculate total slots needed per week for this subject.
     * Lectures (L) + Tutorials (T) = Lecture slots
     * Practicals (P) = Lab slots (often 1 practical session = 2 or more slots)
     */
    public function totalWeeklySlots(): int
    {
        // Fallback to legacy weekly_hours if LTP not set
        $lectureSum = ($this->lecture_hours ?: 0) + ($this->tutorial_hours ?: 0);
        if ($lectureSum === 0 && $this->weekly_hours > 0 && !$this->is_lab) {
            $lectureSum = $this->weekly_hours;
        }

        $labSlots = 0;
        if ($this->practical_hours > 0) {
            // Usually GTU practical hours are the total hours, 
            // but we need to know how many slots that translates to.
            // If practical_hours = 4 and lab_duration = 2, it means 2 sessions.
            $labSlots = $this->practical_hours; 
        } elseif ($this->is_lab && $this->weekly_hours > 0) {
            $labSlots = $this->weekly_hours;
        }

        return $lectureSum + $labSlots;
    }
}
