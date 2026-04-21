<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class AuditTrailMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! Schema::hasTable('audit_logs')) {
            return $response;
        }

        $method = strtoupper((string) $request->method());
        // previously we skipped logging for GET/HEAD/OPTIONS but that meant
        // unauthorized page access wasn't captured.  Only skip simple read
        // requests when they returned success.
        if (in_array($method, ['GET', 'HEAD', 'OPTIONS'], true)) {
            if ($response->getStatusCode() < 400) {
                return $response;
            }
            // otherwise continue to log the error response below
        }

        $user = $request->user();
        $routeName = $request->route()?->getName();

        AuditLog::query()->create([
            'user_id' => $user?->id,
            'role' => $user?->role,
            'action' => $routeName ?: "{$method} {$request->path()}",
            'method' => $method,
            'route_name' => $routeName,
            'path' => '/'.ltrim($request->path(), '/'),
            'status_code' => $response->getStatusCode(),
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 500),
            'meta' => [
                'query' => $request->query(),
            ],
        ]);

        return $response;
    }
}
