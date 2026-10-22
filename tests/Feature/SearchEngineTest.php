<?php

namespace Tests\Feature;

use App\Models\ListingSearchIndex;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SearchEngineTest extends TestCase
{
    use RefreshDatabase;

    public function test_listing_is_indexed_when_published()
    {
        $owner = User::factory()->create();
        $property = Property::create([
            'title' => 'Searchable Property',
            'property_code' => 'IDX-001',
            'owner_user_id' => $owner->id,
            'bedrooms' => 3,
        ]);

        $listing = $property->listings()->create([
            'purpose' => 'For Sale',
            'price' => 5000000,
            'status' => 'draft',
            'owner_actor_id' => $owner->id,
        ]);

        $this->assertDatabaseMissing('listing_search_indices', ['listing_id' => $listing->id]);

        $listing->update(['status' => 'published']);

        // Process jobs (since sync driver is used in testing, job runs immediately)

        $this->assertDatabaseHas('listing_search_indices', [
            'listing_id' => $listing->id,
            'purpose' => 'For Sale',
            'bedrooms' => 3,
        ]);
    }

    public function test_listing_is_removed_from_index_when_deleted()
    {
        $owner = User::factory()->create();
        $property = Property::create([
            'title' => 'Temporary Property',
            'property_code' => 'IDX-002',
            'owner_user_id' => $owner->id,
        ]);

        $listing = $property->listings()->create([
            'purpose' => 'For Rent',
            'price' => 10000,
            'status' => 'published',
            'owner_actor_id' => $owner->id,
        ]);

        $this->assertDatabaseHas('listing_search_indices', ['listing_id' => $listing->id]);

        $listing->delete();

        $this->assertDatabaseMissing('listing_search_indices', ['listing_id' => $listing->id]);
    }

    public function test_search_endpoint_filters_correctly()
    {
        ListingSearchIndex::create([
            'listing_id' => Str::uuid(),
            'title' => 'Cheap Apartment',
            'purpose' => 'For Rent',
            'price' => 5000,
            'currency' => 'ETB',
            'bedrooms' => 1,
            'published_at' => now(),
        ]);

        ListingSearchIndex::create([
            'listing_id' => Str::uuid(),
            'title' => 'Luxury Villa',
            'purpose' => 'For Sale',
            'price' => 20000000,
            'currency' => 'ETB',
            'bedrooms' => 5,
            'published_at' => now(),
        ]);

        // Test price filter
        $response = $this->getJson('/api/v1/search?max_price=10000');
        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('Cheap Apartment', $response->json('data.0.title'));

        // Test bedrooms & purpose filter
        $response2 = $this->getJson('/api/v1/search?purpose=For Sale&bedrooms=4');
        $response2->assertStatus(200);
        $this->assertCount(1, $response2->json('data'));
        $this->assertEquals('Luxury Villa', $response2->json('data.0.title'));
    }
}
