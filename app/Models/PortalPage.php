<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PortalPage extends Model
{
    protected $table = 'pages';

    protected $fillable = [
        'name',
        'route',
        'module_key',
    ];

    public function rolePermissions()
    {
        return $this->hasMany(RolePagePermission::class, 'page_id');
    }
}
