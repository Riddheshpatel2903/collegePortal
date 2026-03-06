<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeatureToggle extends Model
{
    protected $primaryKey = 'feature_key';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'feature_key',
        'enabled',
    ];

    protected $casts = [
        'enabled' => 'boolean',
    ];
}

