<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModuleSetting extends Model
{
    protected $primaryKey = 'module_key';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'module_key',
        'enabled',
    ];

    protected $casts = [
        'enabled' => 'boolean',
    ];
}
