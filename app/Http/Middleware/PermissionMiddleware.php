<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\PortalAccessService;
use Illuminate\Support\Facades\Log;

/**
 * Class PermissionMiddleware
 * 
 * Validates that the authenticated user possesses a specific granular RBAC permission.
 * Acts as the second layer (Gated Actions) in the security hierarchy.
 */
class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $permission
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = auth()->user();

        // 1. Permission Validation
        // This middleware specifically handles Granular Permission Strings (RBAC).
        // It relies on the User model's hasPermission contract.
        $hasPermission = $user->hasPermission($permission);

        // 2. Debug Strategy & Decision
        if (!$hasPermission) {
            Log::info('PermissionCheck Denied', [
                'user_id'    => $user->id,
                'role'       => $user->role,
                'permission' => $permission,
                'path'       => $request->getPathInfo()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => "Restricted: You lack the '{$permission}' permission."
                ], 403);
            }

            abort(403, "Access Denied: The required permission '{$permission}' is not assigned to your role.");
        }

        return $next($request);
    }
}

