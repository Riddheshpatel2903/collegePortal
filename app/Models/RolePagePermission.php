<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RolePagePermission extends Model
{
    protected $fillable = [
        'role_id',
        'page_id',
        'can_view',
        'can_create',
        'can_edit',
        'can_delete',
        'can_export',
    ];

    protected $casts = [
        'can_view' => 'boolean',
        'can_create' => 'boolean',
        'can_edit' => 'boolean',
        'can_delete' => 'boolean',
        'can_export' => 'boolean',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function page()
    {
        return $this->belongsTo(PortalPage::class, 'page_id');
    }
}
