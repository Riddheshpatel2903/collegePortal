<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicPhase extends Model
{
    protected $fillable = [
        'phase_name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getPhaseIndexAttribute(): int
    {
        return strcasecmp($this->phase_name, 'Even') === 0 ? 2 : 1;
    }
}
