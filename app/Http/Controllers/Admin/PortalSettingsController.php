<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PortalAccessService;
use Illuminate\Http\Request;

class PortalSettingsController extends Controller
{
    public function __construct(private PortalAccessService $accessService)
    {
    }

    public function index()
    {
        $this->accessService->syncDefaults();

        return view('admin.settings.index', [
            'roles' => $this->accessService->roles(),
            'permissions' => $this->accessService->permissions(),
            'rolePermissionMatrix' => $this->accessService->rolePermissionMatrix(),
            'pages' => $this->accessService->pagesWithPermissions(),
            'features' => $this->accessService->featureToggles(),
            'modules' => $this->accessService->moduleSettings(),
            'teacherMaxLecturesPerDay' => $this->accessService->teacherMaxLecturesPerDay(),
            // settings for timetable/days/slots form
            'availableDays' => config('timetable.days', []),
            'workingDays' => $this->accessService->workingDays(),
            'maxSlots' => count(config('timetable.slot_blocks', [])),
            'auditLogs' => \App\Models\AuditLog::with('user')->latest()->paginate(20),
        ]);
    }

    public function updatePagePermissions(Request $request)
    {
        $this->accessService->updatePagePermissions((array) $request->input('permissions', []));

        return back()->with('success', 'Page visibility permissions updated.');
    }

    public function updateFeatureToggles(Request $request)
    {
        $featureKeys = $this->accessService->featureToggles()->pluck('feature_key')->all();
        $payload = [];
        foreach ($featureKeys as $key) {
            $payload[$key] = $request->boolean("features.{$key}");
        }

        $this->accessService->updateFeatureToggles($payload);
        return back()->with('success', 'Feature toggles updated.');
    }

    public function updateRolePermissions(Request $request)
    {
        $this->accessService->updateRolePermissions((array) $request->input('permissions_matrix', []));

        return back()->with('success', 'Role permissions updated.');
    }

    public function updateModuleSettings(Request $request)
    {
        $moduleKeys = $this->accessService->moduleSettings()->pluck('module_key')->all();
        $payload = [];
        foreach ($moduleKeys as $key) {
            $payload[$key] = $request->boolean("modules.{$key}");
        }

        $this->accessService->updateModuleSettings($payload);
        return back()->with('success', 'Module settings updated.');
    }
    public function updateSmartSettings(Request $request)
    {
        $validated = $request->validate([
            'teacher_max_lectures_per_day' => ['required', 'integer', 'min:1', 'max:12'],
            'working_days' => ['nullable', 'array'],
            'working_days.*' => ['string', 'in:' . implode(',', config('timetable.days', []))],
        ]);

        $this->accessService->updateSettings([
            'teacher_max_lectures_per_day' => (string) $validated['teacher_max_lectures_per_day'],
            'default_working_days' => json_encode($validated['working_days'] ?? []),
        ]);

        return back()->with('success', 'System preferences updated successfully.');
    }

    public function updateGeneralSettings(Request $request)
    {
        $validated = $request->validate([
            'college_name' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'academic_year' => 'nullable|string|max:20',
            'enable_notifications' => 'nullable|boolean',
            'maintenance_mode' => 'nullable|boolean',
        ]);

        $settingsToUpdate = [];
        foreach ($validated as $key => $value) {
            if ($value !== null) {
                // strict boolean formatting
                if (is_bool($value)) {
                    $value = $value ? '1' : '0';
                }
                $settingsToUpdate[$key] = (string) $value;
            }
        }

        // if checkbox wasn't sent, it means 0
        if (!$request->has('enable_notifications')) {
            $settingsToUpdate['enable_notifications'] = '0';
        }
        if (!$request->has('maintenance_mode')) {
            $settingsToUpdate['maintenance_mode'] = '0';
        }

        if (!empty($settingsToUpdate)) {
            $this->accessService->updateSettings($settingsToUpdate);
        }

        return back()->with('success', 'General settings updated successfully.');
    }

    public function storeRole(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50', 'unique:roles,name'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $roleName = str_replace(' ', '_', strtolower($validated['name']));

        \App\Models\Role::create([
            'name' => $roleName,
            'description' => $validated['description'] ?? 'Custom role created by admin.',
            'is_system' => false,
        ]);

        return back()->with('success', 'Custom Role created successfully.');
    }

    public function updateRole(Request $request, \App\Models\Role $role)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50', \Illuminate\Validation\Rule::unique('roles')->ignore($role->id)],
        ]);

        $role->update([
            'name' => str_replace(' ', '_', strtolower($validated['name']))
        ]);

        return back()->with('success', 'Role updated successfully.');
    }

    public function destroyRole(\App\Models\Role $role)
    {
        if (\App\Models\User::where('role', $role->name)->exists()) {
            return back()->with('error', 'Cannot delete a role that is assigned to active users.');
        }

        $role->delete();

        return back()->with('success', 'Role deleted successfully.');
    }
}
