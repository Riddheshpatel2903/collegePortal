<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::where('email', 'ce.student1@college.test')->first();
$service = app(\App\Services\PortalAccessService::class);
$canView = $service->canViewPage('student.assignments.index', $user);

$page = \App\Models\PortalPage::where('route', 'student.assignments.*')->first();
$role = \App\Models\Role::where('name', 'student')->first();
$perm = \App\Models\RolePagePermission::where('role_id', $role->id)->where('page_id', $page->id)->first();

echo "canView (service): " . ($canView ? 'true' : 'false') . "\n";
echo "Page exists: " . ($page ? 'true' : 'false') . "\n";
echo "Permission can_view: " . ($perm ? ($perm->can_view ? 'true' : 'false') : 'null') . "\n";
echo "Module Key: " . ($page ? $page->module_key : 'null') . "\n";
echo "Module Enabled: " . ($page ? ($service->moduleEnabled($page->module_key) ? 'true' : 'false') : 'null') . "\n";
