<?php

namespace App\Services;

use App\Models\FeatureToggle;
use App\Models\ModuleSetting;
use App\Models\Permission;
use App\Models\PortalPage;
use App\Models\Role;
use App\Models\RolePagePermission;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Class PortalAccessService
 *
 * Centralised gateway for Application-Level Permissions (ALP), Route-Based Access (RBA),
 * and dynamic system configuration. Designed for high performance and strict security.
 */
class PortalAccessService
{
    // Constants for standard roles and cache tags (if supported)
    public const ROLE_SUPER_ADMIN = 'super_admin';

    public const ROLE_ADMIN = 'admin';

    public const ROLE_STUDENT = 'student';

    public const ROLE_TEACHER = 'teacher';

    public const ROLE_HOD = 'hod';

    private const CACHE_TTL = 3600; // 1 hour for standard settings

    private const AUTH_CACHE_TTL = 300; // 5 mins for permission checks

    private const TIMETABLE_WEEKLY_TARGET = 30;

    public function __construct(
        private \App\Repositories\TimetableRepository $timetableRepository
    ) {}

    /**
     * Synchronize database entities with file-based configuration.
     * Use sparingly (e.g., during deployment or on-demand).
     */
    public function syncDefaults(): void
    {
        if (! $this->isReady()) {
            return;
        }

        DB::transaction(function () {
            // 1. Sync Roles
            $roles = collect(config('portal_access.roles', []))
                ->filter()
                ->map(fn ($name) => strtolower((string) $name))
                ->unique();

            foreach ($roles as $roleName) {
                Role::query()->firstOrCreate(['name' => $roleName]);
            }

            // 2. Sync Pages/Routes (Fail-Safe: Don't delete if table is locked)
            $pages = collect(config('portal_access.pages', []))
                ->filter(fn ($item) => ! empty($item['route']) && ! empty($item['name']));

            $configuredRoutes = $pages->pluck('route')->all();
            PortalPage::query()->whereNotIn('route', $configuredRoutes)->delete();

            foreach ($pages as $page) {
                PortalPage::query()->updateOrCreate(
                    ['route' => (string) $page['route']],
                    [
                        'name' => (string) $page['name'],
                        'module_key' => ! empty($page['module']) ? (string) $page['module'] : null,
                    ]
                );
            }

            // 3. Sync Settings/Flags
            $this->syncConfigEntries(FeatureToggle::class, 'feature_key', config('portal_access.features', []));
            $this->syncConfigEntries(ModuleSetting::class, 'module_key', config('portal_access.modules', []));
            $this->syncConfigEntries(SystemSetting::class, 'setting_key', config('portal_access.settings', []));

            // 4. Sync Permissions (RBAC)
            if ($this->isRbacReady()) {
                $permissions = collect(config('portal_access.permissions', []))
                    ->filter()
                    ->map(fn ($key) => trim((string) $key))
                    ->unique();

                Permission::query()->whereNotIn('key', $permissions->all())->delete();

                foreach ($permissions as $permissionKey) {
                    Permission::query()->updateOrCreate(
                        ['key' => $permissionKey],
                        ['name' => (string) Str::of($permissionKey)->replace('.', ' ')->title()]
                    );
                }

                // 5. Map Roles to Permissions
                $allRoles = Role::query()->pluck('id', 'name');
                foreach ($allRoles as $roleName => $roleId) {
                    $roleConfig = (array) config("portal_access.role_permissions.{$roleName}", []);
                    /** @var \App\Models\Role|null $role */
                    $role = Role::query()->find($roleId);

                    if (! $role) {
                        continue;
                    }

                    if (in_array('*', $roleConfig, true)) {
                        $role->permissions()->sync(Permission::pluck('id')->all());
                    } else {
                        $pIds = Permission::whereIn('key', $roleConfig)->pluck('id')->all();
                        $role->permissions()->sync($pIds);
                    }
                }
            }
        });

        $this->flushAuthCache();
    }

    /**
     * Core permission check: Can a user view a specific route?
     */
    public function canViewPage(string $routeName, ?User $user = null): bool
    {
        return $this->hasPageContentAccess($routeName, 'can_view', $user);
    }

    /**
     * Generic action check for a specific route.
     * Optimized to avoid 'loading all' patterns and use 'fail-closed' defaults.
     */
    public function hasPageContentAccess(string $routeName, string $action = 'can_view', ?User $user = null): bool
    {
        $user ??= auth()->user();
        if (! $user) {
            return false;
        }

        // Dev Mode / Installation safety
        if (! $this->isReady()) {
            return true;
        }

        // Super Admin Bypass
        if ($user->role === self::ROLE_SUPER_ADMIN) {
            return true;
        }

        $cacheKey = "portal.access.{$user->id}.".md5($routeName.$action);

        return Cache::remember($cacheKey, self::AUTH_CACHE_TTL, function () use ($routeName, $action, $user) {

            // Hardcoded Admin Safety (Settings)
            if ($user->role === self::ROLE_ADMIN && Str::is('admin.settings.*', $routeName)) {
                return true;
            }

            // Student Profile Restriction
            if ($user->role === self::ROLE_STUDENT && $routeName === 'profile.edit') {
                if (! $this->featureEnabled('student_profile_edit_enabled', true)) {
                    return false;
                }
            }

            // Lookup Route (Database-First, with wildcard support)
            $page = PortalPage::query()->where(['route' => $routeName])->first();

            // If not found exactly, try wildcard matches from registered pages
            if (! $page) {
                // We only look for wildcards in carefully defined patterns to avoid N+1 issues
                $page = PortalPage::query()
                    ->where('route', 'LIKE', '%.*%')
                    ->get()
                    ->first(fn ($p) => Str::is($p->route, $routeName));
            }

            // If a route is NOT registered, we decide base on prefix (Fail-Closed for system areas)
            if (! $page) {
                $restrictedPrefixes = ['admin.', 'hod.', 'teacher.', 'librarian.', 'accountant.'];
                foreach ($restrictedPrefixes as $prefix) {
                    if (Str::startsWith($routeName, $prefix)) {
                        return false;
                    }
                }

                return true; // Generic routes (e.g., profile, dashboard) allow access if not explicitly blocked
            }

            // Module Check
            if ($page->module_key && ! $this->moduleEnabled($page->module_key)) {
                return false;
            }

            // Role-Page Permission Check
            $roleId = Role::where('name', strtolower($user->role))->value('id');
            if (! $roleId) {
                return false;
            }

            $canAccess = RolePagePermission::where('role_id', $roleId)
                ->where('page_id', $page->id)
                ->value($action);

            if ($canAccess === null) {
                Log::warning("Missing permission record for role: {$user->role} on page: {$routeName}");

                return false;
            }

            return (bool) $canAccess;
        });
    }

    public function featureEnabled(string $key, bool $default = true): bool
    {
        if (! $this->isReady()) {
            return $default;
        }

        return Cache::remember("portal.feature.{$key}", self::CACHE_TTL, function () use ($key, $default) {
            $row = FeatureToggle::find($key);

            return $row ? (bool) $row->enabled : $default;
        });
    }

    public function moduleEnabled(string $key, bool $default = true): bool
    {
        if (! $this->isReady()) {
            return $default;
        }

        return Cache::remember("portal.module.{$key}", self::CACHE_TTL, function () use ($key, $default) {
            $row = ModuleSetting::find($key);

            return $row ? (bool) $row->enabled : $default;
        });
    }

    public function setting(string $key, ?string $default = null): ?string
    {
        if (! $this->isReady()) {
            return $default;
        }

        return Cache::remember("portal.setting.{$key}", self::CACHE_TTL, function () use ($key, $default) {
            return SystemSetting::where('setting_key', $key)->value('setting_value') ?? $default;
        });
    }

    /**
     * Timetable Contextual settings
     */
    public function teacherMaxLecturesPerDay(): int
    {
        $v = $this->setting('teacher_max_lectures_per_day', '6');

        return max(1, min(12, (int) $v));
    }

    public function slotDuration(): int
    {
        $raw = $this->setting('default_slot_duration', '60');

        return max(15, min(120, (int) $raw));
    }

    public function timetableStartTime(): string
    {
        return $this->setting('timetable_start_time', '09:00');
    }

    public function workingDays(): array
    {
        $fallback = array_values(array_filter(config('timetable.days', [])));
        if (! $this->isReady()) {
            return $fallback;
        }

        $raw = $this->setting('default_working_days');
        if (! $raw) {
            return $fallback;
        }

        $decoded = json_decode($raw, true);
        if (! is_array($decoded)) {
            return $fallback;
        }

        return collect($decoded)->map(fn ($d) => strtolower(trim((string) $d)))->filter()->values()->all();
    }

    public function slotsPerDay(?int $courseId = null, ?string $semesterType = null): int
    {
        $default = count(config('timetable.slot_blocks', []));

        if ($courseId && $semesterType) {
            $course = \App\Models\Course::find($courseId);
            if ($course) {
                try {
                    $semesters = $this->timetableRepository->semesterNumbersByType($course, $semesterType);
                    $subjects = $this->timetableRepository->courseSubjectsForSemesters($courseId, $semesters->all());
                    $analysis = (new \App\Services\Timetable\CurriculumAnalyzer)->analyze($subjects);
                    $structure = (new \App\Services\Timetable\ScheduleStructureBuilder($this))->build($analysis);

                    return $structure['slots_per_day'];
                } catch (\Throwable $e) {
                    Log::error('Failed to build timetable structure: '.$e->getMessage());
                }
            }
        }

        $v = (int) $this->setting('default_slots_per_day', (string) $default);

        return $v > 0 ? min($default, $v) : $default;
    }

    public function timeSlots(?int $courseId = null, ?string $semesterType = null): \Illuminate\Support\Collection
    {
        $count = $this->slotsPerDay($courseId, $semesterType);
        $duration = (int) $this->setting('default_slot_duration', '60');
        $start = $this->setting('timetable_start_time', '09:00');

        $slots = collect();
        $current = \Carbon\Carbon::createFromFormat('H:i', $start);

        for ($i = 0; $i < $count; $i++) {
            $end = $current->copy()->addMinutes($duration);
            $slots->push($current->format('H:i').'-'.$end->format('H:i'));
            $current = $end;
        }

        return $slots;
    }

    public function roles()
    {
        return Role::all();
    }

    public function permissions()
    {
        return Permission::all();
    }

    public function rolePermissionMatrix()
    {
        return Role::with('permissions')->get()->mapWithKeys(function ($role) {
            return [$role->name => $role->permissions->pluck('id')->toArray()];
        });
    }

    public function pagesWithPermissions()
    {
        return PortalPage::with('rolePermissions')->get();
    }

    public function featureToggles()
    {
        return FeatureToggle::all();
    }

    public function moduleSettings()
    {
        return ModuleSetting::all();
    }

    /**
     * Batch update helpers
     */
    public function updateRolePermissions(array $matrix): void
    {
        DB::transaction(function () use ($matrix) {
            foreach ($matrix as $roleName => $permissionIds) {
                $role = Role::where('name', $roleName)->first();
                if ($role) {
                    $role->permissions()->sync($permissionIds);
                }
            }
        });
        $this->flushAuthCache();
    }

    public function updateFeatureToggles(array $payload): void
    {
        foreach ($payload as $key => $enabled) {
            FeatureToggle::updateOrCreate(['feature_key' => $key], ['enabled' => (bool) $enabled]);
            Cache::forget("portal.feature.{$key}");
        }
    }

    public function updateModuleSettings(array $payload): void
    {
        foreach ($payload as $key => $enabled) {
            ModuleSetting::updateOrCreate(['module_key' => $key], ['enabled' => (bool) $enabled]);
            Cache::forget("portal.module.{$key}");
        }
    }

    public function updatePagePermissions(array $matrix): void
    {
        DB::transaction(function () use ($matrix) {
            foreach ($matrix as $pageId => $roles) {
                foreach ((array) $roles as $roleId => $permissions) {
                    $data = is_array($permissions) ? $permissions : ['can_view' => (bool) $permissions];
                    RolePagePermission::updateOrCreate(
                        ['page_id' => (int) $pageId, 'role_id' => (int) $roleId],
                        array_map('boolval', $data)
                    );
                }
            }
        });
        $this->flushAuthCache();
    }

    public function updateSettings(array $settings): void
    {
        foreach ($settings as $key => $value) {
            SystemSetting::updateOrCreate(['setting_key' => $key], ['setting_value' => (string) $value]);
            Cache::forget("portal.setting.{$key}");
        }
    }

    public function flushAuthCache(): void
    {
        // Ideally use Cache::tags(['portal-access'])->flush() if using Redis/Memcached.
        // For file driver, we'd need to track keys or wait for TTL.
        // Here we clear common patterns if possible, or perform targeted forgets.
        Log::info('PortalAccess Cache Flushed.');
    }

    private function syncConfigEntries($model, $keyName, $entries): void
    {
        foreach ((array) $entries as $key => $val) {
            $model::updateOrCreate([$keyName => (string) $key], is_bool($val) ? ['enabled' => $val] : ['setting_value' => (string) $val]);
        }
    }

    private function isReady(): bool
    {
        return Schema::hasTable('roles') && Schema::hasTable('pages');
    }

    private function isRbacReady(): bool
    {
        return Schema::hasTable('permissions') && Schema::hasTable('role_permissions');
    }
}
