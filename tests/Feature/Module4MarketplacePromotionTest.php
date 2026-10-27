<?php

namespace Tests\Feature;

use App\Models\ApiToken;
use App\Models\Listing;
use App\Models\ListingSearchIndex;
use App\Models\Property;
use App\Models\PropertyMedia;
use App\Models\User;
use App\Services\Listing\ListingPromotionService;
use App\Services\Listing\ListingQualityScorer;
use Database\Seeders\AuthorizationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class Module4MarketplacePromotionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AuthorizationSeeder::class);
    }

    public function test_listing_quality_scorer_calculates_dynamically_based_on_attributes(): void
    {
        $scorer = app(ListingQualityScorer::class);

        $property = Property::create([
            'id' => (string) Str::uuid(),
            'property_code' => 'PROP-'.Str::random(6),
            'title' => 'Luxury Penthouse in Bole',
            'description' => str_repeat('Spacious luxury penthouse featuring panoramic city views and modern finishes. ', 10),
            'latitude' => 8.9806,
            'longitude' => 38.7578,
            'status' => 'verified',
        ]);

        // Attach 5 media items
        for ($i = 0; $i < 5; $i++) {
            PropertyMedia::create([
                'id' => (string) Str::uuid(),
                'property_id' => $property->id,
                'kind' => 'image',
                'storage_key' => "properties/photo-{$i}.jpg",
                'status' => 'processed',
            ]);
        }

        $listing = Listing::create([
            'id' => (string) Str::uuid(),
            'property_id' => $property->id,
            'purpose' => 'For Sale',
            'price' => 15000000,
            'currency' => 'ETB',
            'status' => 'draft',
        ]);

        $score = $scorer->calculate($listing);
        $this->assertGreaterThanOrEqual(80, $score);
        $this->assertLessThanOrEqual(100, $score);

        $breakdown = $scorer->getBreakdown($listing);
        $this->assertCount(5, $breakdown);
    }

    public function test_listing_model_auto_calculates_and_persists_quality_score_on_saving(): void
    {
        $property = Property::create([
            'id' => (string) Str::uuid(),
            'property_code' => 'PROP-'.Str::random(6),
            'title' => 'Standard Apartment',
            'description' => 'A basic two-bedroom apartment in a quiet residential neighborhood with easy road access.',
            'latitude' => 9.01,
            'longitude' => 38.76,
            'status' => 'active',
        ]);

        $listing = new Listing([
            'id' => (string) Str::uuid(),
            'property_id' => $property->id,
            'purpose' => 'For Rent',
            'price' => 25000,
            'currency' => 'ETB',
            'status' => 'draft',
        ]);
        $listing->save();

        $this->assertDatabaseHas('listings', [
            'id' => $listing->id,
        ]);
        $listing->refresh();
        $this->assertGreaterThan(0, $listing->quality_score);
    }

    public function test_api_promote_listing_creates_active_promotion(): void
    {
        $owner = User::factory()->create();
        $token = Str::random(40);
        ApiToken::create(['user_id' => $owner->id, 'name' => 'test-token', 'token' => hash('sha256', $token)]);

        $property = Property::create([
            'id' => (string) Str::uuid(),
            'property_code' => 'PROP-'.Str::random(6),
            'title' => 'Featured Commercial Building',
            'owner_user_id' => $owner->id,
            'status' => 'active',
        ]);

        $listing = Listing::create([
            'id' => (string) Str::uuid(),
            'property_id' => $property->id,
            'purpose' => 'For Sale',
            'price' => 50000000,
            'status' => 'published',
            'owner_actor_id' => $owner->id,
        ]);

        $response = $this->withToken($token)->postJson("/api/v1/listings/{$listing->id}/promote", [
            'tier' => 'featured',
            'duration_days' => 14,
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('data.tier', 'featured');
        $response->assertJsonPath('data.status', 'active');

        $this->assertDatabaseHas('promotions', [
            'listing_id' => $listing->id,
            'tier' => 'featured',
            'status' => 'active',
        ]);

        $listing->refresh();
        $this->assertNotNull($listing->activePromotion);
        $this->assertEquals('featured', $listing->activePromotion->tier);
    }

    public function test_search_prioritizes_promoted_listings_at_the_top(): void
    {
        $regularListingId = (string) Str::uuid();
        $promotedListingId = (string) Str::uuid();

        // 1. Regular published listing published newer (should normally be first by published_at)
        ListingSearchIndex::create([
            'listing_id' => $regularListingId,
            'title' => 'Standard Family Home in Addis',
            'price' => 4000000,
            'currency' => 'ETB',
            'purpose' => 'sale',
            'published_at' => now(),
        ]);

        // 2. Promoted listing published older
        ListingSearchIndex::create([
            'listing_id' => $promotedListingId,
            'title' => 'Sponsored Prime Villa in Addis',
            'price' => 8000000,
            'currency' => 'ETB',
            'purpose' => 'sale',
            'published_at' => now()->subDays(5),
        ]);

        $property = Property::create([
            'id' => (string) Str::uuid(),
            'property_code' => 'PROP-'.Str::random(6),
            'title' => 'Sponsored Prime Villa in Addis',
        ]);

        $listing = Listing::create([
            'id' => $promotedListingId,
            'property_id' => $property->id,
            'purpose' => 'sale',
            'price' => 8000000,
            'status' => 'published',
        ]);

        // Promote the older listing
        $promoService = app(ListingPromotionService::class);
        $promo = $promoService->promote($listing, 'premium', 7);

        // Run search query
        $response = $this->getJson('/api/v1/search?q=Addis');
        $response->assertStatus(200);

        $results = $response->json('data');
        $this->assertCount(2, $results);

        // Even though regular was published more recently, the promoted one must be ranked first!
        $this->assertEquals($promotedListingId, $results[0]['listing_id']);
        $this->assertEquals($regularListingId, $results[1]['listing_id']);

        // Test recording click on promotion
        $promoService->recordClick($promo);
        $promo->refresh();
        $this->assertEquals(1, $promo->clicks_count);
    }
}
