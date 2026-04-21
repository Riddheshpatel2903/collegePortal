<?php

use App\Models\Role;
use App\Models\PortalPage;
use App\Models\RolePagePermission;
use Illuminate\Support\Str;

$roles = Role::where('name', '!=', 'super_admin')->get();
$pages = PortalPage::all();

$prefixes = [
    'admin' => 'admin.',
    'hod' => 'hod.',
    'teacher' => 'teacher.',
    'student' => 'student.',
    'accountant' => 'accountant.',
    'librarian' => 'librarian.',
];

$count = 0;
foreach ($roles as $role) {
    $roleName = strtolower($role->name);
    $myPrefix = $prefixes[$roleName] ?? null;

    if (!$myPrefix) continue;

    echo "Syncing permissions for role: {$roleName} (Prefix: {$myPrefix})\n";

    foreach ($pages as $page) {
        $allowed = false;
        
        // 1. Check prefix match
        if (Str::startsWith($page->route, $myPrefix)) {
            $allowed = true;
        }
        
        // 2. Allow common pages
        if (in_array($page->route, ['profile.edit'])) {
            $allowed = true;
        }

        // 3. If not allowed, ensure can_view is false
        if (!$allowed) {
            $perm = RolePagePermission::where('role_id', $role->id)
                ->where('page_id', $page->id)
                ->first();
                
            if ($perm && $perm->can_view) {
                $perm->update(['can_view' => false]);
                $count++;
            }
        }
    }
}

echo "Done. Disabled {$count} unauthorized cross-role page permissions.\n";
