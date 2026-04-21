<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'subject_id',
        'course_id',
        'academic_year',
        'semester_number',
        'semester_id',
        'title',
        'description',
        'total_marks',
        'due_date',
        'attachment_path',
        'status',
        'allow_late_submission',
        'late_until',
        'is_active',
    ];

    protected $dates = [
        'due_date',
        'late_until',
    ];

    // ==========================
    // RELATIONSHIPS
    // ==========================

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function submissions()
    {
        return $this->hasMany(AssignmentSubmission::class);
    }

    // ==========================
    // BUSINESS LOGIC
    // ==========================

    public function isLate()
    {
        return Carbon::now()->gt($this->due_date);
    }

    public function isSubmissionAllowed()
    {
        if (! $this->isLate()) {
            return true;
        }

        if ($this->allow_late_submission && $this->late_until) {
            return Carbon::now()->lte($this->late_until);
        }

        return false;
    }
}
