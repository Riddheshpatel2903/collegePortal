<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'user.active' => \App\Http\Middleware\CheckUserActiveMiddleware::class,
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'permission' => \App\Http\Middleware\PermissionMiddleware::class,
            'page.access' => \App\Http\Middleware\CheckPageAccess::class,
            'feature' => \App\Http\Middleware\CheckFeatureToggle::class,
            'module' => \App\Http\Middleware\CheckModuleEnabled::class,
            'maintenance.guard' => \App\Http\Middleware\MaintenanceModeGuard::class,
            'no.edit' => \App\Http\Middleware\PreventEditWhenDisabled::class,
        ]);
        $middleware->web(append: [
            \App\Http\Middleware\MaintenanceModeGuard::class,
            \App\Http\Middleware\AuditTrailMiddleware::class,
            \App\Http\Middleware\PreventEditWhenDisabled::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->is('api/*') || $request->wantsJson() || $request->ajax()) {
                $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;

                return response()->json([
                    'success' => false,
                    'message' => config('app.debug') ? $e->getMessage() : 'An unexpected error occurred.',
                    'data' => null,
                ], $status);
            }

            if (! config('app.debug') && $e instanceof \Exception && ! ($e instanceof \Illuminate\Validation\ValidationException)) {
                $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
                if ($status == 404) {
                    return response()->view('errors.404', [], 404);
                }
                if ($status >= 500) {
                    return response()->view('errors.500', [], 500);
                }
            }
        });
    })->create();
