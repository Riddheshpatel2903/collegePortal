<?php

namespace App\Http\Middleware;

use App\Services\PortalAccessService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckModuleEnabled
{
    public function __construct(private PortalAccessService $accessService) {}

    public function handle(Request $request, Closure $next, string $moduleKey): Response
    {
        if (! $this->accessService->moduleEnabled($moduleKey, true)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Module is disabled by administrator.'], 403);
            }
            abort(403, 'Module is disabled by administrator.');
        }

        return $next($request);
    }
}
