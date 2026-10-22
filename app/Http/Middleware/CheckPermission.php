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
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (! $user) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'error' => [
                        'code' => 'UNAUTHORIZED',
                        'message' => 'Authentication required.',
                    ],
                ], 401);
            }

            return redirect()->guest(route('login'));
        }

        // Get organization from header if it exists
        $organizationId = $request->header('X-Organization-ID');

        if (! $user->hasAnyPermission($permission, $organizationId)) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'error' => [
                        'code' => 'FORBIDDEN',
                        'message' => 'You do not have permission to perform this action.',
                    ],
                ], 403);
            }

            abort(403, 'Unauthorized access. You do not have the required permission.');
        }

        return $next($request);
    }
}
