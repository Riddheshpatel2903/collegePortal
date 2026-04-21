<?php

namespace App\Http\Middleware;

use App\Services\PortalAccessService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class RoleMiddleware
 *
 * Validates that the authenticated user possesses one of the required roles.
 * Acts as the first layer in the security hierarchy.
 */
class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = auth()->user();

        // 1. Normalise and Process Roles
        $allowedRoles = collect($roles)
            ->filter()
            ->map(fn ($role) => strtolower(trim((string) $role)))
            ->values()
            ->all();

        // Allow automatic inheritance for Super Admins
        if (in_array(PortalAccessService::ROLE_ADMIN, $allowedRoles, true) &&
            ! in_array(PortalAccessService::ROLE_SUPER_ADMIN, $allowedRoles, true)) {
            $allowedRoles[] = PortalAccessService::ROLE_SUPER_ADMIN;
        }

        // 2. Validation Decision
        $userRole = strtolower((string) $user->role);
        $hasRole = ! empty($allowedRoles) && in_array($userRole, $allowedRoles, true);

        // 3. Debug Strategy & Decision
        if (! $hasRole) {
            Log::info('RoleCheck Failure: Insufficient Role permissions', [
                'user_id' => $user->id,
                'user_role' => $userRole,
                'required' => $allowedRoles,
                'path' => $request->getPathInfo(),
            ]);

            abort(403, 'Access Restricted: Your role does not have permission for this section.');
        }

        return $next($request);
    }
}
