<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Result extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'course_id',
        'academic_year',
        'semester_number',
        'sgpa',
        'cgpa',
        'total_credits_earned',
        'backlog_subjects',
        'result_status',
        'promoted',
        'result_declared_date',
        'locked_at',
        'locked_by',
    ];

    protected $casts = [
        'sgpa' => 'decimal:2',
        'cgpa' => 'decimal:2',
        'promoted' => 'boolean',
        'result_declared_date' => 'date',
        'locked_at' => 'datetime',
    ];

    // Relationships
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function lockedBy()
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    public function resultSubjects()
    {
        return $this->hasMany(ResultSubject::class);
    }

    // Scopes
    public function scopePassed($query)
    {
        return $query->where('result_status', 'pass');
    }

    public function scopeFailed($query)
    {
        return $query->where('result_status', 'fail');
    }

    public function scopeSearchStudent($query, ?string $term)
    {
        $search = trim((string) $term);
        if ($search === '') {
            return $query;
        }

        return $query->whereHas('student', function ($sq) use ($search) {
            $sq->where('roll_number', 'like', "%{$search}%")
                ->orWhere('gtu_enrollment_no', 'like', "%{$search}%")
                ->orWhere('name', 'like', "%{$search}%")
                ->orWhere('id', is_numeric($search) ? (int) $search : 0)
                ->orWhereHas('user', function ($uq) use ($search) {
                    $uq->where('name', 'like', "%{$search}%");
                });
        });
    }
}
