<?php

namespace Tests\Feature;

use App\Domains\Advertising\Models\MarketingCampaign;
use App\Domains\Subscription\Models\Subscription;
use App\Domains\Subscription\Models\SubscriptionPlan;
use App\Models\Listing;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class Module14SubscriptionAdvertisingTest extends TestCase
{
    use RefreshDatabase;

    public function test_free_tier_user_blocked_when_exceeding_listing_quota_on_api(): void
    {
        $user = User::factory()->create();

        // Populate 3 active listings (reaching default free tier limit of 3)
        for ($i = 1; $i <= 3; $i++) {
            $property = Property::create([
                'title' => "Property {$i}",
                'property_code' => "PROP-{$i}",
                'owner_user_id' => $user->id,
                'status' => 'verified',
            ]);

            Listing::create([
                'property_id' => $property->id,
                'owner_actor_id' => $user->id,
                'purpose' => 'For Sale',
                'status' => 'published',
                'price' => 1500000,
            ]);
        }

        $newProperty = Property::create([
            'title' => 'Excess Property',
            'property_code' => 'PROP-EXCESS',
            'owner_user_id' => $user->id,
            'status' => 'verified',
        ]);

        // Attempting to post to /api/v1/properties/{property}/listings
        $response = $this->actingAs($user)
            ->postJson("/api/v1/properties/{$newProperty->id}/listings", [
                'purpose' => 'For Sale',
                'price' => 2000000,
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'error' => 'SUBSCRIPTION_LIMIT_REACHED',
                'max_allowed' => 3,
                'current_count' => 3,
            ]);

        // Attempting to post to /api/v1/listings direct
        $responseDirect = $this->actingAs($user)
            ->postJson('/api/v1/listings', [
                'property_id' => $newProperty->id,
                'purpose' => 'For Sale',
                'price' => 2000000,
            ]);

        $responseDirect->assertStatus(403)
            ->assertJson([
                'error' => 'SUBSCRIPTION_LIMIT_REACHED',
            ]);
    }

    public function test_free_tier_user_blocked_when_exceeding_quota_on_web_listing_store(): void
    {
        $user = User::factory()->create();

        for ($i = 1; $i <= 3; $i++) {
            $property = Property::create([
                'title' => "Web Property {$i}",
                'property_code' => "WEB-{$i}",
                'owner_user_id' => $user->id,
                'status' => 'verified',
            ]);

            Listing::create([
                'property_id' => $property->id,
                'owner_actor_id' => $user->id,
                'purpose' => 'For Rent',
                'status' => 'active',
                'price' => 15000,
            ]);
        }

        $newProperty = Property::create([
            'title' => 'Web 4th Property',
            'property_code' => 'WEB-4',
            'owner_user_id' => $user->id,
            'status' => 'verified',
        ]);

        $response = $this->actingAs($user)
            ->from('/dashboard/listings/create')
            ->post('/dashboard/listings', [
                'property_id' => $newProperty->id,
                'purpose' => 'For Rent',
                'price' => 25000,
                'currency' => 'ETB',
                'action' => 'publish',
            ]);

        $response->assertRedirect('/dashboard/listings/create')
            ->assertSessionHas('error');
    }

    public function test_subscribed_user_with_pro_quota_can_create_additional_listings(): void
    {
        $user = User::factory()->create();

        $plan = SubscriptionPlan::create([
            'name' => 'Broker Pro',
            'slug' => 'broker-pro',
            'price' => 1999.00,
            'max_listings' => 25,
            'is_active' => true,
        ]);

        Subscription::create([
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
            'current_period_start' => now()->subDay(),
            'current_period_end' => now()->addMonth(),
        ]);

        // Create 3 listings
        for ($i = 1; $i <= 3; $i++) {
            $property = Property::create([
                'title' => "Pro Property {$i}",
                'property_code' => "PRO-{$i}",
                'owner_user_id' => $user->id,
                'status' => 'verified',
            ]);

            Listing::create([
                'property_id' => $property->id,
                'owner_actor_id' => $user->id,
                'purpose' => 'For Sale',
                'status' => 'published',
                'price' => 3000000,
            ]);
        }

        $newProperty = Property::create([
            'title' => '4th Property Allowed Under Pro Plan',
            'property_code' => 'PRO-4',
            'owner_user_id' => $user->id,
            'status' => 'verified',
        ]);

        $response = $this->actingAs($user)
            ->postJson("/api/v1/properties/{$newProperty->id}/listings", [
                'purpose' => 'For Sale',
                'price' => 4500000,
            ]);

        $response->assertStatus(201);
        $this->assertEquals(4, Listing::where('owner_actor_id', $user->id)->count());
    }

    public function test_ad_impression_tracking_increments_campaign_counter(): void
    {
        $campaign = MarketingCampaign::create([
            'name' => 'Ayat Luxury Villas Launch',
            'channel' => 'web',
            'placement' => 'home_header',
            'headline' => 'Exclusive Luxury Villas in Ayat',
            'subheadline' => 'Now available with 20% down payment and 5-year installment plan.',
            'target_url' => 'https://example.com/ayat-villas',
            'start_date' => now()->subDays(2),
            'status' => 'active',
            'impressions_count' => 0,
            'clicks_count' => 0,
        ]);

        $response = $this->postJson("/api/v1/ads/{$campaign->id}/impression");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'campaign_id' => $campaign->id,
                'impressions_count' => 1,
            ]);

        $this->assertEquals(1, $campaign->fresh()->impressions_count);

        // Multiple impressions increment appropriately
        $this->postJson("/api/v1/ads/{$campaign->id}/impression");
        $this->assertEquals(2, $campaign->fresh()->impressions_count);
    }

    public function test_ad_click_tracking_increments_counter_and_redirects(): void
    {
        $campaign = MarketingCampaign::create([
            'name' => 'Bole Commercial Center',
            'channel' => 'web',
            'placement' => 'home_header',
            'headline' => 'Prime Bole Retail Spaces',
            'target_url' => 'https://example.com/bole-commercial',
            'start_date' => now()->subDays(1),
            'status' => 'active',
            'impressions_count' => 10,
            'clicks_count' => 0,
        ]);

        // JSON API click tracking
        $responseJson = $this->postJson("/api/v1/ads/{$campaign->id}/click");
        $responseJson->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'campaign_id' => $campaign->id,
                'clicks_count' => 1,
                'target_url' => 'https://example.com/bole-commercial',
            ]);

        $this->assertEquals(1, $campaign->fresh()->clicks_count);

        // Web redirect click tracking
        $responseRedirect = $this->get("/api/v1/ads/{$campaign->id}/click");
        $responseRedirect->assertRedirect('https://example.com/bole-commercial');

        $this->assertEquals(2, $campaign->fresh()->clicks_count);
    }

    public function test_ad_banner_blade_component_renders_active_campaign(): void
    {
        $campaign = MarketingCampaign::create([
            'name' => 'Featured Penthouse Promo',
            'channel' => 'web',
            'placement' => 'home_header',
            'headline' => 'Luxury Penthouse in Kazanchis',
            'subheadline' => 'Panoramic skyline views with VIP parking.',
            'cta_text' => 'Book a Private Tour',
            'target_url' => 'https://example.com/kazanchis-penthouse',
            'start_date' => now()->subDay(),
            'status' => 'active',
        ]);

        $rendered = Blade::render('<x-ad-banner placement="home_header" />');

        $this->assertStringContainsString('Luxury Penthouse in Kazanchis', $rendered);
        $this->assertStringContainsString('Panoramic skyline views', $rendered);
        $this->assertStringContainsString('Book a Private Tour', $rendered);
        $this->assertStringContainsString('Sponsored', $rendered);
        $this->assertStringContainsString($campaign->id, $rendered);
    }
}
