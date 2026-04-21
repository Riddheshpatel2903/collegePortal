<?php

namespace App\Http\Middleware;

use App\Services\PortalAccessService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CheckPageAccess
 *
 * The ultimate gatekeeper for the College Portal.
 * Orchestrates a unified security flow: User Integrity -> Action Mapping -> ALP Validation.
 */
class CheckPageAccess
{
    /**
     * Handle an incoming request.
     *
     * Flow:
     * 1. Identity Check (Is user active? Valid role?)
     * 2. Context Extraction (Route name, namespace safety)
     * 3. Action Mapping (CRUD Translation)
     * 4. Centralised Access Check (PortalAccessService)
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        $route = $request->route();
        $routeName = $route ? $route->getName() : null;

        // Requirement: This middleware focuses on Page + Action validation for named routes.
        if (! $routeName) {
            return $next($request);
        }

        $accessService = app(PortalAccessService::class);

        // 1. Granular Action Mapping
        // Prevents privilege escalation by ensuring standard CRUD routes check specific columns.
        $actionMap = [
            '.create' => 'can_create',
            '.store' => 'can_create',
            '.edit' => 'can_edit',
            '.update' => 'can_edit',
            '.destroy' => 'can_delete',
            '.show' => 'can_view',
            '.index' => 'can_view',
            '.export' => 'can_export',
        ];

        $targetAction = 'can_view';
        $permissionRoute = $routeName;

        foreach ($actionMap as $suffix => $action) {
            if (Str::endsWith($routeName, $suffix)) {
                $targetAction = $action;
                // Collapse to the module's primary identifier for permission lookup
                $permissionRoute = Str::beforeLast($routeName, '.').'.index';
                break;
            }
        }

        // 2. Centralised Access Validation
        $hasAccess = $accessService->hasPageContentAccess($permissionRoute, $targetAction, $user);

        // 3. Predictable Debug Strategy
        if (config('app.debug') || ! $hasAccess) {
            Log::info('CheckPageAccess Unified Trace', [
                'user' => $user->id,
                'role' => $user->role,
                'intent' => $routeName,
                'resolved' => $permissionRoute,
                'action' => $targetAction,
                'verdict' => $hasAccess ? 'ALLOWED' : 'DENIED',
            ]);
        }

        if (! $hasAccess) {
            $message = config('app.debug')
                ? "Unified Flow Block: Missing '{$targetAction}' for '{$permissionRoute}'"
                : 'Security Restricted: You lack the necessary permissions for this module.';

            abort(403, $message);
        }

        return $next($request);
    }
}
