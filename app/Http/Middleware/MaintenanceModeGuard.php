<?php

namespace App\Http\Middleware;

use App\Services\PortalAccessService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MaintenanceModeGuard
{
    public function __construct(private PortalAccessService $accessService)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (!$this->accessService->featureEnabled('maintenance_mode', false)) {
            return $next($request);
        }

        $path = ltrim($request->path(), '/');
        $isAdminAuthPath = $request->is('admin') || $request->is('admin/*');
        $isAuthenticatedAdmin = $request->user() && $request->user()->role === 'admin';

        if ($isAdminAuthPath || $isAuthenticatedAdmin) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Portal is in maintenance mode.'], 503);
        }

        abort(503, 'Portal is in maintenance mode. Only admin access is allowed.');
    }
}

