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
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class PortalAccessService
{
    private const TIMETABLE_WEEKLY_TARGET = 30;

    public function __construct(
        private \App\Repositories\TimetableRepository $timetableRepository
    ) {
    }

    public function syncDefaults(): void
    {
        if (!$this->isReady()) {
            return;
        }

        DB::transaction(function () {
            $roles = collect(config('portal_access.roles', []))
                ->filter()
                ->map(fn($name) => strtolower((string) $name))
                ->unique()
                ->values();

            foreach ($roles as $roleName) {
                Role::query()->firstOrCreate(['name' => $roleName]);
            }

            $pages = collect(config('portal_access.pages', []))
                ->filter(fn($item) => !empty($item['route']) && !empty($item['name']))
                ->values();
            $configuredRoutes = $pages->pluck('route')->map(fn($route) => (string) $route)->all();
            PortalPage::query()->whereNotIn('route', $configuredRoutes)->delete();

            foreach ($pages as $page) {
                PortalPage::query()->updateOrCreate(
                    ['route' => (string) $page['route']],
                    [
                        'name' => (string) $page['name'],
                        'module_key' => !empty($page['module']) ? (string) $page['module'] : null,
                    ]
                );
            }

            $allRoles = Role::query()->pluck('id', 'name');
            $allPages = PortalPage::query()->pluck('id');
            foreach ($allRoles as $roleName => $roleId) {
                foreach ($allPages as $pageId) {
                    RolePagePermission::query()->firstOrCreate(
                        ['role_id' => (int) $roleId, 'page_id' => (int) $pageId],
                        ['can_view' => true]
                    );
                }
            }

            foreach ((array) config('portal_access.features', []) as $featureKey => $enabled) {
                FeatureToggle::query()->updateOrCreate(
                    ['feature_key' => (string) $featureKey],
                    ['enabled' => (bool) $enabled]
                );
            }

            foreach ((array) config('portal_access.modules', []) as $moduleKey => $enabled) {
                ModuleSetting::query()->updateOrCreate(
                    ['module_key' => (string) $moduleKey],
                    ['enabled' => (bool) $enabled]
                );
            }

            foreach ((array) config('portal_access.settings', []) as $settingKey => $value) {
                SystemSetting::query()->updateOrCreate(
                    ['setting_key' => (string) $settingKey],
                    ['setting_value' => (string) $value]
                );
            }

            if ($this->isRbacReady()) {
                $permissions = collect(config('portal_access.permissions', []))
                    ->filter()
                    ->map(fn($key) => trim((string) $key))
                    ->unique()
                    ->values();

                Permission::query()->whereNotIn('key', $permissions->all())->delete();

                foreach ($permissions as $permissionKey) {
                    Permission::query()->updateOrCreate(
                        ['key' => $permissionKey],
                        ['name' => Str::of($permissionKey)->replace('.', ' ')->title()->toString()]
                    );
                }

                $rolePermissionMap = (array) config('portal_access.role_permissions', []);
                foreach ($allRoles as $roleName => $roleId) {
                    $role = Role::query()->find((int) $roleId);
                    if (!$role) {
                        continue;
                    }

                    $configured = (array) ($rolePermissionMap[$roleName] ?? []);
                    if (in_array('*', $configured, true)) {
                        $permissionIds = Permission::query()->pluck('id')->all();
                        $role->permissions()->sync($permissionIds);
                        continue;
                    }

                    $permissionIds = Permission::query()
                        ->whereIn('key', $configured)
                        ->pluck('id')
                        ->all();
                    $role->permissions()->sync($permissionIds);
                }
            }
        });

        $this->flushCache();
    }

    public function canViewPage(string $routeName, ?User $user = null): bool
    {
        return $this->hasPageContentAccess($routeName, 'can_view', $user);
    }

    public function hasPageContentAccess(string $routeName, string $action = 'can_view', ?User $user = null): bool
    {
        $user ??= auth()->user();
        if (!$user) {
            return false;
        }

        if (!$this->isReady()) {
            return true;
        }

        // Super Admin gets unrestricted access always
        if ($user->role === 'super_admin') {
            return true;
        }

        $cacheKey = "portal.page.{$user->id}.{$routeName}.{$action}";
        return Cache::remember($cacheKey, 300, function () use ($routeName, $action, $user) {

            // Allow admin unrestricted access to settings pages
            if ($user->role === 'admin' && Str::is('admin.settings.*', $routeName)) {
                return true;
            }

            if ($routeName === 'profile.edit' && $user->role === 'student' && !$this->featureEnabled('student_profile_edit_enabled', true)) {
                return false;
            }

            $pages = PortalPage::query()->get(['id', 'route', 'module_key']);
            $page = $pages->first(fn($row) => $row->route === $routeName)
                ?? $pages->first(fn($row) => Str::contains((string) $row->route, '*') && Str::is((string) $row->route, $routeName));
            if (!$page) {
                // By default, if it's not registered as a restricted page, allow access
                return true;
            }

            if ($page->module_key && !$this->moduleEnabled((string) $page->module_key)) {
                return false;
            }

            $roleId = Role::query()->where('name', strtolower((string) $user->role))->value('id');
            if (!$roleId) {
                return false;
            }

            $permission = RolePagePermission::query()
                ->where('role_id', (int) $roleId)
                ->where('page_id', (int) $page->id)
                ->first();

            if (!$permission) {
                return false;
            }

            return (bool) ($permission->{$action} ?? false);
        });
    }

    public function featureEnabled(string $key, bool $default = true): bool
    {
        if (!$this->isReady()) {
            return $default;
        }

        $cacheKey = "portal.feature.{$key}";
        return Cache::remember($cacheKey, 300, function () use ($key, $default) {
            $row = FeatureToggle::query()->find($key);
            if (!$row) {
                return $default;
            }
            return (bool) $row->enabled;
        });
    }

    public function moduleEnabled(string $key, bool $default = true): bool
    {
        if (!$this->isReady()) {
            return $default;
        }

        $cacheKey = "portal.module.{$key}";
        return Cache::remember($cacheKey, 300, function () use ($key, $default) {
            $row = ModuleSetting::query()->find($key);
            if (!$row) {
                return $default;
            }
            return (bool) $row->enabled;
        });
    }

    public function setting(string $key, ?string $default = null): ?string
    {
        if (!$this->isReady()) {
            return $default;
        }

        $cacheKey = "portal.setting.{$key}";
        return Cache::remember($cacheKey, 300, function () use ($key, $default) {
            return SystemSetting::query()->where('setting_key', $key)->value('setting_value') ?? $default;
        });
    }

    public function teacherMaxLecturesPerDay(): int
    {
        $raw = $this->setting(
            'teacher_max_lectures_per_day',
            (string) config('portal_access.settings.teacher_max_lectures_per_day', '6')
        );
        return max(1, min(12, (int) ($raw ?? 6)));
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

    /**
     * Return the configured working days for the timetable. Stored as a JSON
     * array in system_settings under "default_working_days". Falls back to
     * the values from config/timetable.php so existing behaviour is preserved.
     */
    public function workingDays(): array
    {
        $fallback = array_values(array_filter(config('timetable.days', [])));
        if (!$this->isReady()) {
            return $fallback;
        }

        $raw = $this->setting('default_working_days');
        if ($raw === null) {
            return $fallback;
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return $fallback;
        }

        $normalized = collect($decoded)
            ->map(fn ($day) => strtolower(trim((string) $day)))
            ->filter()
            ->values()
            ->all();

        if (empty($normalized)) {
            return $fallback;
        }

        $allowed = array_map('strtolower', $fallback);
        $filtered = array_values(array_filter($normalized, fn ($day) => in_array($day, $allowed, true)));

        return !empty($filtered) ? $filtered : $fallback;
    }

    /**
     * Number of slots that should be shown/used per day. The value is stored
     * as an integer string in system_settings under "default_slots_per_day".
     * Falling back to the number of configured slot blocks.
     */
    public function slotsPerDay(?int $courseId = null, ?string $semesterType = null): int
    {
        $default = count(config('timetable.slot_blocks', []));
        if ($courseId && $semesterType) {
            $course = \App\Models\Course::find($courseId);
            if ($course) {
                $semesters = $this->timetableRepository->semesterNumbersByType($course, $semesterType);
                $subjects = $this->timetableRepository->courseSubjectsForSemesters($courseId, $semesters->all());
                
                $analyzer = new \App\Services\Timetable\CurriculumAnalyzer();
                $analysis = $analyzer->analyze($subjects);
                
                $builder = new \App\Services\Timetable\ScheduleStructureBuilder($this);
                $structure = $builder->build($analysis);
                
                return $structure['slots_per_day'];
            }
        }

        if (!$this->isReady()) {
            return $default;
        }

        $raw = $this->setting('default_slots_per_day', (string) $default);
        $rawValue = (int) ($raw ?? $default);

        if ($rawValue < 1) {
            $dayCount = max(1, count($this->workingDays()));
            $recommended = intdiv(self::TIMETABLE_WEEKLY_TARGET, $dayCount);
            if ($recommended >= 1 && $recommended <= $default && ($recommended * $dayCount) === self::TIMETABLE_WEEKLY_TARGET) {
                return $recommended;
            }

            return $default;
        }

        return max(1, min($default, $rawValue));
    }

    /**
     * Convenience helper that returns the array of time‑slot strings taking the
     * configured slots‑per‑day into account.
     */
    public function timeSlots(?int $courseId = null, ?string $semesterType = null): \Illuminate\Support\Collection
    {
        $slotsCount = $this->slotsPerDay($courseId, $semesterType);
        $duration = $this->slotDuration();
        $startTime = $this->timetableStartTime();

        $slots = collect();
        $current = \Carbon\Carbon::createFromFormat('H:i', $startTime);

        for ($i = 0; $i < $slotsCount; $i++) {
            $end = $current->copy()->addMinutes($duration);
            $slots->push($current->format('H:i') . '-' . $end->format('H:i'));
            $current = $end;
        }

        return $slots;
    }

    public function pagesWithPermissions()
    {
        return PortalPage::query()
            ->with(['rolePermissions.role'])
            ->orderBy('name')
            ->get();
    }

    public function roles()
    {
        return Role::query()->orderBy('name')->get();
    }

    public function permissions()
    {
        if (!$this->isRbacReady()) {
            return collect();
        }

        return Permission::query()->orderBy('name')->get();
    }

    public function rolePermissionMatrix(): array
    {
        if (!$this->isRbacReady()) {
            return [];
        }

        return DB::table('role_permissions')
            ->select(['role_id', 'permission_id'])
            ->get()
            ->groupBy('role_id')
            ->map(fn($rows) => collect($rows)->pluck('permission_id')->map(fn($id) => (int) $id)->all())
            ->toArray();
    }

    public function featureToggles()
    {
        return FeatureToggle::query()->orderBy('feature_key')->get();
    }

    public function moduleSettings()
    {
        return ModuleSetting::query()->orderBy('module_key')->get();
    }

    public function updatePagePermissions(array $matrix): void
    {
        if (!$this->isReady()) {
            return;
        }

        DB::transaction(function () use ($matrix) {
            foreach ($matrix as $pageId => $roles) {
                foreach ((array) $roles as $roleId => $permissions) {

                    // Backward compatibility: If the payload is just a boolean, map it to `can_view`
                    if (!is_array($permissions)) {
                        $permissions = ['can_view' => (bool) $permissions];
                    }

                    RolePagePermission::query()->updateOrCreate(
                        ['page_id' => (int) $pageId, 'role_id' => (int) $roleId],
                        [
                            'can_view' => (bool) ($permissions['can_view'] ?? false),
                            'can_create' => (bool) ($permissions['can_create'] ?? false),
                            'can_edit' => (bool) ($permissions['can_edit'] ?? false),
                            'can_delete' => (bool) ($permissions['can_delete'] ?? false),
                            'can_export' => (bool) ($permissions['can_export'] ?? false),
                        ]
                    );
                }
            }
        });

        $this->flushCache();
    }

    public function updateFeatureToggles(array $featureFlags): void
    {
        if (!$this->isReady()) {
            return;
        }

        DB::transaction(function () use ($featureFlags) {
            foreach ($featureFlags as $key => $enabled) {
                FeatureToggle::query()->updateOrCreate(
                    ['feature_key' => (string) $key],
                    ['enabled' => (bool) $enabled]
                );
            }
        });

        $this->flushCache();
    }

    public function updateModuleSettings(array $moduleFlags): void
    {
        if (!$this->isReady()) {
            return;
        }

        DB::transaction(function () use ($moduleFlags) {
            foreach ($moduleFlags as $key => $enabled) {
                ModuleSetting::query()->updateOrCreate(
                    ['module_key' => (string) $key],
                    ['enabled' => (bool) $enabled]
                );
            }
        });

        $this->flushCache();
    }

    public function updateSettings(array $settings): void
    {
        if (!$this->isReady()) {
            return;
        }

        DB::transaction(function () use ($settings) {
            foreach ($settings as $key => $value) {
                SystemSetting::query()->updateOrCreate(
                    ['setting_key' => (string) $key],
                    ['setting_value' => (string) $value]
                );
            }
        });

        $this->flushCache();
    }

    public function updateRolePermissions(array $matrix): void
    {
        if (!$this->isRbacReady()) {
            return;
        }

        DB::transaction(function () use ($matrix) {
            foreach ($matrix as $roleId => $permissionMap) {
                $permissionIds = collect((array) $permissionMap)
                    ->filter(fn($enabled) => (bool) $enabled)
                    ->keys()
                    ->map(fn($permissionId) => (int) $permissionId)
                    ->values()
                    ->all();

                $role = Role::query()->find((int) $roleId);
                if ($role) {
                    $role->permissions()->sync($permissionIds);
                }
            }
        });

        $this->flushCache();
    }

    public function flushCache(): void
    {
        Cache::flush();
    }

    private function isReady(): bool
    {
        return Schema::hasTable('roles')
            && Schema::hasTable('pages')
            && Schema::hasTable('role_page_permissions')
            && Schema::hasTable('feature_toggles')
            && Schema::hasTable('module_settings')
            && Schema::hasTable('system_settings');
    }

    private function isRbacReady(): bool
    {
        return Schema::hasTable('roles')
            && Schema::hasTable('permissions')
            && Schema::hasTable('role_permissions');
    }
}
