<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'start_year',
        'end_year',
        'session_start_date',
        'session_end_date',
        'is_current',
        'status',
    ];

    protected $casts = [
        'session_start_date' => 'date',
        'session_end_date' => 'date',
        'is_current' => 'boolean',
    ];

    // Relationships
    public function semesters()
    {
        return $this->hasMany(Semester::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class);
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

    // Mutators
    protected static function boot()
    {
        parent::boot();

        // Ensure only one current session
        static::saving(function ($session) {
            if ($session->is_current) {
                static::where('is_current', true)
                    ->where('id', '!=', $session->id)
                    ->update(['is_current' => false]);
            }
        });
    }
}
