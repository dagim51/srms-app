<?php

namespace App\Http\Middleware;

use App\Domains\Subscription\Models\Subscription;
use App\Models\Listing;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceSubscriptionLimits
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        // Determine max allowed active listings
        $activeSubscription = Subscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->with('plan')
            ->first();

        $maxListings = $activeSubscription?->plan?->max_listings ?? 3; // Default free tier quota

        // Count current active listings
        $currentListingsCount = Listing::where('owner_actor_id', $user->id)
            ->whereIn('status', ['active', 'published', 'pending'])
            ->count();

        if ($currentListingsCount >= $maxListings) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'error' => 'SUBSCRIPTION_LIMIT_REACHED',
                    'message' => "You have reached your subscription limit of {$maxListings} active listings. Please upgrade your plan.",
                    'max_allowed' => $maxListings,
                    'current_count' => $currentListingsCount,
                ], 403);
            }

            return redirect()->back()->with('error', "You have reached your limit of {$maxListings} active listings. Please upgrade your subscription.");
        }

        return $next($request);
    }
}
