<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, $permission): Response
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => ['code' => 'UNAUTHORIZED', 'message' => 'Unauthorized.']], 401);
        }

        // Get organization from header if it exists
        $organizationId = $request->header('X-Organization-ID');

        if (!$user->hasPermissionTo($permission, $organizationId)) {
            return response()->json(['error' => ['code' => 'FORBIDDEN', 'message' => 'You do not have permission to perform this action.']], 403);
        }

        return $next($request);
    }
}
