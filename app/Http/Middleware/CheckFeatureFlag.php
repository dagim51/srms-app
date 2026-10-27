<?php

namespace App\Http\Middleware;

use App\Domains\Administration\Models\FeatureFlag;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckFeatureFlag
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $user = $request->user();
        $role = $user?->roles()?->first()?->name ?? $user?->role ?? null;

        if (! FeatureFlag::isEnabled($feature, $role)) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'error' => 'FEATURE_DISABLED',
                    'message' => "The feature '{$feature}' is currently disabled.",
                    'feature' => $feature,
                ], 403);
            }

            abort(403, "The feature '{$feature}' is currently disabled.");
        }

        return $next($request);
    }
}
