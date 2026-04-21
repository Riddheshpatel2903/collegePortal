<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Leave extends Model
{
    use HasFactory;

    protected $fillable = [
        'leaveable_type',
        'leaveable_id',
        'start_date',
        'end_date',
        'leave_type',
        'requested_by_role',
        'reason',
        'attachment',
        'status',
        'current_stage',
        'approved_by',
        'approval_remarks',
        'approved_at',
        'applied_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'approved_at' => 'datetime',
        'applied_at' => 'datetime',
    ];

    // Polymorphic relationship
    public function leaveable()
    {
        return $this->morphTo();
    }

    public function approvedLeaves()
    {
        return $this->morphMany(Leave::class, 'leaveable', null, 'approved_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Accessor
    public function getTotalDaysAttribute()
    {
        return $this->start_date && $this->end_date ? $this->start_date->diffInDays($this->end_date) + 1 : 0;
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeForDepartment(Builder $query, int $departmentId): Builder
    {
        return $query->whereHasMorph(
            'leaveable',
            [Student::class, Teacher::class],
            fn (Builder $morph) => $morph->where('department_id', $departmentId)
        );
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['search'] ?? null, fn (Builder $q, string $search) => $this->applySearch($q, $search))
            ->when($filters['status'] ?? null, fn (Builder $q, string $status) => $q->where('status', $status))
            ->when($filters['role'] ?? null, fn (Builder $q, string $role) => $this->applyRoleFilter($q, $role))
            ->when($filters['from_date'] ?? null, fn (Builder $q, string $from) => $q->whereDate('end_date', '>=', $from))
            ->when($filters['to_date'] ?? null, fn (Builder $q, string $to) => $q->whereDate('start_date', '<=', $to));
    }

    public function scopeWithApplicantRelations(Builder $query): Builder
    {
        return $query->with([
            'approver:id,name',
            'leaveable' => function (MorphTo $morphTo) {
                $morphTo->morphWith([
                    Student::class => ['user:id,name,email', 'department:id,name'],
                    Teacher::class => ['user:id,name,email', 'department:id,name'],
                ]);
            },
        ]);
    }

    private function applySearch(Builder $query, string $search): Builder
    {
        $term = trim($search);
        if ($term === '') {
            return $query;
        }

        return $query->where(function (Builder $outer) use ($term) {
            $outer
                ->whereHasMorph('leaveable', [Student::class, Teacher::class], function (Builder $morph) use ($term) {
                    $morph
                        ->whereHas('user', function (Builder $user) use ($term) {
                            $user
                                ->where('name', 'like', "%{$term}%")
                                ->orWhere('email', 'like', "%{$term}%");
                        })
                        ->orWhereHas('department', fn (Builder $department) => $department->where('name', 'like', "%{$term}%"));
                });
        });
    }

    private function applyRoleFilter(Builder $query, string $role): Builder
    {
        return match ($role) {
            'student' => $query->where('leaveable_type', Student::class),
            'teacher' => $query->where('leaveable_type', Teacher::class),
            'hod' => $query->where('requested_by_role', 'hod'),
            default => $query,
        };
    }
}
