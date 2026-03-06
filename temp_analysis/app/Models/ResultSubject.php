<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResultSubject extends Model
{
    use HasFactory;

    protected $fillable = [
        'result_id',
        'subject_id',
        'student_id',
        'internal_marks',
        'external_marks',
        'max_marks',
        'grade',
        'grade_point',
        'credits',
        'is_backlog',
        'subject_status'
    ];

    protected $casts = [
        'internal_marks' => 'decimal:2',
        'external_marks' => 'decimal:2',
        'max_marks' => 'decimal:2',
        'grade_point' => 'decimal:2',
        'is_backlog' => 'boolean',
    ];

    // Relationships
    public function result()
    {
        return $this->belongsTo(Result::class);
    }

    public function semesterSubject()
    {
        return $this->belongsTo(SemesterSubject::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    // Accessors
    public function getTotalMarksAttribute()
    {
        return $this->internal_marks + $this->external_marks;
    }

    public function getPercentageAttribute()
    {
        return $this->max_marks > 0 ? (($this->internal_marks + $this->external_marks) / $this->max_marks) * 100 : 0;
    }

    // Auto-calculate grade and grade point
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($resultSubject) {
            $percentage = $resultSubject->percentage;
            
            if ($percentage >= 90) {
                $resultSubject->grade = 'A+';
                $resultSubject->grade_point = 10;
            } elseif ($percentage >= 80) {
                $resultSubject->grade = 'A';
                $resultSubject->grade_point = 9;
            } elseif ($percentage >= 70) {
                $resultSubject->grade = 'B+';
                $resultSubject->grade_point = 8;
            } elseif ($percentage >= 60) {
                $resultSubject->grade = 'B';
                $resultSubject->grade_point = 7;
            } elseif ($percentage >= 50) {
                $resultSubject->grade = 'C+';
                $resultSubject->grade_point = 6;
            } elseif ($percentage >= 40) {
                $resultSubject->grade = 'C';
                $resultSubject->grade_point = 5;
            } else {
                $resultSubject->grade = 'F';
                $resultSubject->grade_point = 0;
                $resultSubject->is_backlog = true;
                $resultSubject->subject_status = 'fail';
            }
        });
    }
}
