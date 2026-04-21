<?php

namespace App\Http\Middleware;

use App\Services\PortalAccessService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventEditWhenDisabled
{
    public function __construct(private PortalAccessService $accessService) {}

    public function handle(Request $request, Closure $next): Response
    {
        // block any update-style request if the global edit toggle is off
        if (! $this->accessService->featureEnabled('edit_button_enabled', true)
            && in_array(strtoupper((string) $request->method()), ['PUT', 'PATCH'], true)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Editing is currently disabled.'], 403);
            }
            abort(403, 'Editing is currently disabled.');
        }

        return $next($request);
    }
}
