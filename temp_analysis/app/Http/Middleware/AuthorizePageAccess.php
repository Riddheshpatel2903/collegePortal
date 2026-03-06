<?php

namespace App\Http\Middleware;

use App\Services\PortalAccessService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthorizePageAccess
{
    public function __construct(private PortalAccessService $accessService)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $routeName = $request->route()?->getName();
        if (!$routeName || !$request->user()) {
            return $next($request);
        }

        // Determine the action based on the route name ending
        $action = 'can_view';
        if (str_ends_with($routeName, '.create') || str_ends_with($routeName, '.store')) {
            $action = 'can_create';
        } elseif (str_ends_with($routeName, '.edit') || str_ends_with($routeName, '.update')) {
            $action = 'can_edit';
        } elseif (str_ends_with($routeName, '.destroy')) {
            $action = 'can_delete';
        } elseif (str_ends_with($routeName, 'export')) {
            $action = 'can_export';
        }

        if (!$this->accessService->hasPageContentAccess($routeName, $action, $request->user())) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized page access.'], 403);
            }
            abort(403, 'You do not have permission to perform this action.');
        }

        return $next($request);
    }
}

