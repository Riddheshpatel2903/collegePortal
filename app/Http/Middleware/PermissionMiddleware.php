<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();
        if (!$user || $user->status !== 'active') {
            abort(403, 'Unauthorized action.');
        }

        // when RBAC tables are absent treat permission checks as no‑ops.
        if (!\Illuminate\Support\Facades\Schema::hasTable('permissions') ||
            !\Illuminate\Support\Facades\Schema::hasTable('role_permissions')
        ) {
            return $next($request);
        }

        if (!$user->hasPermission($permission)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Permission denied.'], 403);
            }
            abort(403, 'Permission denied.');
        }

        return $next($request);
    }
}

