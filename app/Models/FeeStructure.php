<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeeStructure extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'year_number',
        'semester_sequence',
        'semester_number',
        'fee_type',
        'amount',
        'is_mandatory',
        'description',
        'is_active'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_mandatory' => 'boolean',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function studentFees()
    {
        return $this->hasMany(StudentFee::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForSemester($query, $semesterNumber)
    {
        return $query->where('semester_number', $semesterNumber)
            ->orWhere('semester_sequence', $semesterNumber);
    }

    public function scopeForYear($query, int $yearNumber)
    {
        return $query->where('year_number', $yearNumber);
    }

    public function scopeMandatory($query)
    {
        return $query->where('is_mandatory', true);
    }
}
