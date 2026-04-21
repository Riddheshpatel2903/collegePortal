<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CheckUserActiveMiddleware
 *
 * Ensures that the user is both authenticated and has an 'active' status.
 * This is the first layer of the project's security hierarchy.
 */
class CheckUserActiveMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // Standardised status check across the entire portal.
        if (! $user || $user->status !== 'active') {
            Log::warning('Security Check: User not authenticated or inactive', [
                'user_id' => $user->id ?? 'guest',
                'path' => $request->getPathInfo(),
            ]);

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized: Your account is inactive or session is invalid.',
                ], 403);
            }

            // For web requests, we abort with a 403.
            // Note: 'auth' middleware should usually catch guests before this point.
            abort(403, 'Your account is inactive or session is invalid.');
        }

        return $next($request);
    }
}
