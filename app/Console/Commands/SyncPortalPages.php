<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Illuminate\Support\Facades\Route;
use App\Models\PortalPage;
use App\Models\Role;
use App\Models\RolePagePermission;
use Illuminate\Support\Str;

class SyncPortalPages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'portal:sync-pages';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan registered routes and sync portal pages for RBAC settings.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Portal Pages Sync...');

        $routes = collect(Route::getRoutes())->filter(function ($route) {
            $name = $route->getName();
            if (!$name) {
                return false;
            }

            // Only care about GET routes that are mostly pages.
            if (!in_array('GET', $route->methods())) {
                return false;
            }

            // Exclude API routes, ignition, debugbar, etc.
            if (Str::startsWith($name, ['api.', 'ignition.', 'debugbar.', 'sanctum.', 'passport.'])) {
                return false;
            }

            // We want main portal routes. E.g. admin.*, student.*, teacher.*, hod.*, accountant.*
            if (Str::startsWith($name, ['admin.', 'student.', 'teacher.', 'hod.', 'accountant.'])) {
                // Only sync the index endpoints to represent the unified module on the UI
                if (!Str::endsWith($name, '.index')) {
                    return false;
                }
                return true;
            }

            return false;
        });

        $this->info("Found " . $routes->count() . " eligible routes for page registration.");

        $roles = Role::pluck('id', 'name');
        $added = 0;
        $updated = 0;

        foreach ($routes as $route) {
            $name = $route->getName();

            $parts = explode('.', $name);
            $moduleKey = isset($parts[1]) && $parts[1] !== 'index' ? $parts[1] : $parts[0];

            // Format to a friendly "Courses Page", "Events Page" string
            $title = ucwords(str_replace(['_', '-'], ' ', $moduleKey)) . ' Page';

            // Special fallback for literal top level overrides like dashboard if we add them later
            if ($moduleKey === 'settings') {
                $title = 'Settings Page';
            }

            $page = PortalPage::firstOrNew(['route' => $name]);

            if ($page->exists) {
                // $page->name = $title; // We probably don't want to overwrite manual custom names.
                $updated++;
                $page->save();
            } else {
                $page->name = $title;
                $page->module_key = $moduleKey;
                $page->save();
                $added++;

                // Set default permissions. 
                // e.g., if it starts with 'admin.', default give access only to super_admin and admin.
                $routePrefix = $parts[0]; // e.g., 'admin', 'student'

                foreach ($roles as $roleName => $roleId) {
                    $canView = ($roleName === 'super_admin');
                    if ($roleName === $routePrefix) {
                        $canView = true;
                    }

                    RolePagePermission::updateOrCreate(
                        ['role_id' => $roleId, 'page_id' => $page->id],
                        ['can_view' => $canView]
                    );
                }
            }
        }

        // Clean up orphaned pages? (Only run if you are sure routes aren't just temporarily gone)
        $this->newLine();
        $this->info("Completed Sync! Added: {$added}, Updated/Verified: {$updated}.");
    }
}
