<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

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
        if (!$user || $user->status !== 'active') {
            abort(403, 'Unauthorized action.');
        }

        $allowedRoles = collect($roles)->filter()->map(fn ($role) => strtolower((string) $role))->values()->all();
        if (in_array('admin', $allowedRoles, true) && !in_array('super_admin', $allowedRoles, true)) {
            $allowedRoles[] = 'super_admin';
        }

        if (empty($allowedRoles) || !$user->hasRole($allowedRoles)) {
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}
