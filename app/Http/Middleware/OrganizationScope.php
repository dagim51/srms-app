<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OrganizationScope
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $organizationId = $request->header('X-Organization-ID');
        if (!$organizationId) {
            return $next($request);
        }

        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => ['code' => 'UNAUTHORIZED', 'message' => 'Unauthorized.']], 401);
        }

        // Check if user belongs to organization
        $isMember = \Illuminate\Support\Facades\DB::table('organization_members')
            ->where('user_id', $user->id)
            ->where('organization_id', $organizationId)
            ->exists();

        if (!$isMember) {
            return response()->json(['error' => ['code' => 'FORBIDDEN', 'message' => 'You do not belong to this organization.']], 403);
        }

        return $next($request);
    }
}
