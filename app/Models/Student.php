<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'department_id',
        'course_id',
        'academic_session_id',
        'current_year',
        'roll_number',
        'gtu_enrollment_no',
        'registration_number',
        'name',
        'email',
        'phone',
        'date_of_birth',
        'gender',
        'address',
        'cgpa',
        'cpi',
        'backlog_count',
        'student_status',
        'academic_status',
        'admission_date',
        'admission_year',
        'photo',
        'is_active'
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'admission_date' => 'date',
        'cgpa' => 'decimal:2',
        'cpi' => 'decimal:2',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function academicSession()
    {
        return $this->belongsTo(AcademicSession::class);
    }

    public function currentSemester()
    {
        // Legacy compatibility while transitioning away from semester master records.
        return $this->belongsTo(Semester::class, 'current_semester_id');
    }

    /**
     * Alias for currentSemester relationship
     */
    public function semester()
    {
        return $this->currentSemester();
    }

    public function fees()
    {
        return $this->hasMany(StudentFee::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function attendanceRecords()
    {
        return $this->attendances();
    }

    public function results()
    {
        return $this->hasMany(Result::class);
    }

    public function resultSubjects()
    {
        return $this->hasMany(ResultSubject::class);
    }

    public function leaves()
    {
        return $this->morphMany(Leave::class, 'leaveable');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('student_status', 'active');
    }

    public function scopeInSemester($query, $semesterId)
    {
        return $query;
    }

    public function scopeInCurrentYear($query, int $year)
    {
        return $query->where('current_year', $year);
    }

    public function scopeSearch($query, ?string $term)
    {
        $search = trim((string) $term);
        if ($search === '') {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('id', is_numeric($search) ? (int) $search : 0)
                ->orWhere('roll_number', 'like', "%{$search}%")
                ->orWhere('gtu_enrollment_no', 'like', "%{$search}%")
                ->orWhereHas('user', function ($uq) use ($search) {
                    $uq->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
        });
    }

    public function scopeByGtuEnrollment($query, string $enrollmentNo)
    {
        return $query->where('gtu_enrollment_no', strtoupper(trim($enrollmentNo)));
    }

    // Helper Methods
    public function hasBacklogs()
    {
        return $this->backlog_count > 0;
    }

    public function canBePromoted()
    {
        return $this->backlog_count == 0 && $this->student_status == 'active';
    }

    public function getAttendancePercentage($semesterSubjectId = null)
    {
        $query = $this->attendances();

        if ($semesterSubjectId) {
            $query->whereHas('attendanceSession', function ($q) use ($semesterSubjectId) {
                $q->where('semester_subject_id', $semesterSubjectId);
            });
        }

        $totalClasses = $query->count();
        $presentClasses = $query->where('status', 'present')->count();

        return $totalClasses > 0 ? round(($presentClasses / $totalClasses) * 100, 2) : 0;
    }

    public function getCurrentSemesterNumberAttribute(): int
    {
        return app(\App\Services\SemesterCalculationService::class)->currentSemesterForStudent($this);
    }
}
