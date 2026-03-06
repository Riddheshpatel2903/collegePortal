<?php

namespace App\Http\Middleware;

use App\Services\PortalAccessService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckFeatureToggle
{
    public function __construct(private PortalAccessService $accessService)
    {
    }

    public function handle(Request $request, Closure $next, string $featureKey, string $expected = 'true'): Response
    {
        $isEnabled = $this->accessService->featureEnabled($featureKey, true);
        $mustBeEnabled = strtolower($expected) !== 'false';
        $passes = $mustBeEnabled ? $isEnabled : !$isEnabled;

        if (!$passes) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'This feature is currently disabled.'], 403);
            }
            abort(403, 'This feature is currently disabled.');
        }

        return $next($request);
    }
}

