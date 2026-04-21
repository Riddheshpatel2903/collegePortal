<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function student()
    {
        return $this->hasOne(Student::class);
    }

    public function teacher()
    {
        return $this->hasOne(Teacher::class);
    }

    public function notices()
    {
        return $this->hasMany(Notice::class, 'posted_by');
    }

    public function hodDepartment()
    {
        return $this->hasOne(Department::class, 'hod_id');
    }

    protected static function booted()
    {
        static::updated(function ($user) {
            if ($user->wasChanged('role')) {
                $user->deleteOldProfile();
                $user->createProfileByRole();
            }
        });
    }

    public function createProfileByRole()
    {
        if ($this->role === 'student' && ! $this->student) {
            $this->student()->create([]);
        }

        if ($this->role === 'teacher' && ! $this->teacher) {
            $this->teacher()->create([]);
        }
    }

    public function attendanceSessions()
    {
        return $this->hasMany(AttendanceSession::class, 'teacher_id');
    }

    public function deleteOldProfile()
    {
        if ($this->student) {
            $this->student->delete();
        }

        if ($this->teacher) {
            $this->teacher->delete();
        }
    }

    /**
     * Role Check Helpers
     */
    public function hasRole($role)
    {
        if (is_array($role)) {
            return in_array($this->role, $role);
        }

        return $this->role === $role;
    }

    public function isAdmin()
    {
        return in_array($this->role, ['super_admin', 'admin'], true);
    }

    public function isTeacher()
    {
        return $this->role === 'teacher';
    }

    public function isHod()
    {
        return $this->role === 'hod';
    }

    public function isStudent()
    {
        return $this->role === 'student';
    }

    public function isAccountant()
    {
        return $this->role === 'accountant';
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function hasPermission(string $permissionKey): bool
    {
        // fully privileged super_admin bypasses RBAC entirely
        if ($this->isSuperAdmin()) {
            return true;
        }

        // if the permissions tables haven't been created yet, we can't
        // perform a query; assume checks are disabled so the app remains
        // usable until migrations are run.
        if (! \Illuminate\Support\Facades\Schema::hasTable('roles') ||
            ! \Illuminate\Support\Facades\Schema::hasTable('permissions') ||
            ! \Illuminate\Support\Facades\Schema::hasTable('role_permissions')
        ) {
            return true;
        }

        $cacheKey = "user.permission.{$this->id}.{$permissionKey}";

        return Cache::remember($cacheKey, 300, function () use ($permissionKey) {
            $role = Role::query()->where('name', strtolower((string) $this->role))->first();
            if (! $role) {
                return false;
            }

            return $role->permissions()->where('key', $permissionKey)->exists();
        });
    }

    public function canPage(string $routeName): bool
    {
        return app(\App\Services\PortalAccessService::class)->canViewPage($routeName, $this);
    }
}
