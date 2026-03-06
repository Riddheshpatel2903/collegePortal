<?php

namespace App\Providers;

use App\Services\PortalAccessService;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Pagination\Paginator::useTailwind();
        \App\Models\Student::observe(\App\Observers\StudentObserver::class);
        \App\Models\Course::observe(\App\Observers\CourseObserver::class);
        \App\Models\Semester::observe(\App\Observers\SemesterObserver::class);
        \App\Models\AcademicSession::observe(\App\Observers\AcademicSessionObserver::class);

        Blade::if('canPage', function (string $routeName) {
            return app(PortalAccessService::class)->canViewPage($routeName, auth()->user());
        });

        Blade::if('featureEnabled', function (string $featureKey) {
            return app(PortalAccessService::class)->featureEnabled($featureKey, true);
        });

        Blade::if('moduleEnabled', function (string $moduleKey) {
            return app(PortalAccessService::class)->moduleEnabled($moduleKey, true);
        });

        // expose helper to all views so templates can easily access the service
        view()->composer('*', function ($view) {
            $view->with('portalAccess', app(PortalAccessService::class));
        });

        // simple global PHP helper functions
        if (! function_exists('App\Providers\portal_feature_enabled')) {
            function portal_feature_enabled(string $key, bool $default = true): bool
            {
                return app(PortalAccessService::class)->featureEnabled($key, $default);
            }
        }

        if (! function_exists('App\Providers\portal_module_enabled')) {
            function portal_module_enabled(string $key, bool $default = true): bool
            {
                return app(PortalAccessService::class)->moduleEnabled($key, $default);
            }
        }

        if (! function_exists('App\Providers\portal_can_view')) {
            function portal_can_view(string $routeName): bool
            {
                return app(PortalAccessService::class)->canViewPage($routeName, auth()->user());
            }
        }

        if (
            Schema::hasTable('roles')
            && Schema::hasTable('pages')
            && Schema::hasTable('role_page_permissions')
            && Schema::hasTable('feature_toggles')
            && Schema::hasTable('module_settings')
            && Schema::hasTable('system_settings')
        ) {
            app(PortalAccessService::class)->syncDefaults();
        }
    }
}
