<?php

namespace Tests\Feature;

use App\Domains\Subscription\Models\Subscription;
use App\Domains\Subscription\Models\SubscriptionPlan;
use App\Http\Middleware\EnforceSubscriptionLimits;
use App\Models\Listing;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class SubscriptionLimitMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_free_tier_user_blocked_when_exceeding_listing_limit(): void
    {
        $user = User::factory()->create();

        // Create 3 active listings (reaching default free tier quota of 3)
        for ($i = 1; $i <= 3; $i++) {
            $property = Property::create([
                'title' => "Property {$i}",
                'property_code' => "PROP-00{$i}",
                'owner_user_id' => $user->id,
                'status' => 'verified',
            ]);

            Listing::create([
                'property_id' => $property->id,
                'owner_actor_id' => $user->id,
                'purpose' => 'For Sale',
                'status' => 'active',
                'price' => 2000000,
            ]);
        }

        // Attempting to access route protected by subscription.limits
        $response = $this->actingAs($user)
            ->withMiddleware(EnforceSubscriptionLimits::class)
            ->getJson('/api/v1/user/listings');

        // Middleware directly tested
        $request = Request::create('/api/v1/listings', 'POST');
        $request->setUserResolver(fn () => $user);

        $middleware = new EnforceSubscriptionLimits;
        $response = $middleware->handle($request, function () {
            return response()->json(['success' => true]);
        });

        $this->assertEquals(403, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('SUBSCRIPTION_LIMIT_REACHED', $data['error']);
        $this->assertEquals(3, $data['max_allowed']);
    }

    public function test_subscribed_user_with_higher_quota_can_proceed(): void
    {
        $user = User::factory()->create();

        $premiumPlan = SubscriptionPlan::create([
            'name' => 'Agency Pro',
            'slug' => 'agency-pro',
            'price' => 5000.00,
            'max_listings' => 50,
            'is_active' => true,
        ]);

        Subscription::create([
            'user_id' => $user->id,
            'subscription_plan_id' => $premiumPlan->id,
            'status' => 'active',
            'current_period_start' => now()->subDays(5),
            'current_period_end' => now()->addDays(25),
        ]);

        // User currently has 5 active listings (over free tier, well below 50)
        for ($i = 1; $i <= 5; $i++) {
            $property = Property::create([
                'title' => "Agency Property {$i}",
                'property_code' => "AGENCY-00{$i}",
                'owner_user_id' => $user->id,
                'status' => 'verified',
            ]);

            Listing::create([
                'property_id' => $property->id,
                'owner_actor_id' => $user->id,
                'purpose' => 'For Sale',
                'status' => 'active',
                'price' => 5000000,
            ]);
        }

        $request = Request::create('/api/v1/listings', 'POST');
        $request->setUserResolver(fn () => $user);

        $middleware = new EnforceSubscriptionLimits;
        $response = $middleware->handle($request, function () {
            return response()->json(['success' => true]);
        });

        $this->assertEquals(200, $response->getStatusCode());
    }
}
