<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Str;

class CheckPageAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $routeName = $request->route() ? $request->route()->getName() : null;

        // Only process named routes and authenticated users
        if ($routeName && auth()->check()) {
            // Check access through PortalAccessService
            $accessService = app(\App\Services\PortalAccessService::class);

            $permissionRoute = $routeName;

            // Map CRUD routes back to their parent module index for permission checks
            $crudSuffixes = ['.create', '.store', '.show', '.edit', '.update', '.destroy'];
            foreach ($crudSuffixes as $suffix) {
                if (Str::endsWith($routeName, $suffix)) {
                    $permissionRoute = Str::beforeLast($routeName, '.') . '.index';
                    break;
                }
            }

            // Check if user has permission to view this page using mapped route
            $canView = $accessService->canViewPage($permissionRoute);
            \Illuminate\Support\Facades\Log::info('CheckPageAccess Debug', [
                'user_id' => auth()->id(),
                'role' => auth()->user()->role,
                'original_route' => $routeName,
                'mapped_route' => $permissionRoute,
                'can_view' => $canView
            ]);

            if (!$canView) {
                abort(403, 'Access Restricted: You do not have permission to view this page.');
            }
        }

        return $next($request);
    }
}
