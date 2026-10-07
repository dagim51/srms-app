<?php

namespace Tests\Feature;

use App\Models\Amenity;
use App\Models\Listing;
use App\Models\Property;
use App\Models\PropertyType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchEngineWebTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_search_page_loads_successfully_with_published_listings()
    {
        $owner = User::factory()->create();
        $property = Property::create([
            'title' => 'Bole Luxury Apartment',
            'property_code' => 'PROP-TEST-001',
            'owner_user_id' => $owner->id,
            'bedrooms' => 3,
            'bathrooms' => 2,
            'building_area' => 180,
            'status' => 'verified',
            'latitude' => 9.0012,
            'longitude' => 38.7845,
        ]);

        $listing = $property->listings()->create([
            'purpose' => 'For Sale',
            'price' => 15000000,
            'currency' => 'ETB',
            'status' => 'published',
            'visibility' => 'public',
            'owner_actor_id' => $owner->id,
            'published_at' => now(),
        ]);

        $response = $this->get('/search');

        $response->assertStatus(200);
        $response->assertSee('Bole Luxury Apartment');
        $response->assertSee('15,000,000');
        $response->assertViewHas('listings');
        $response->assertViewHas('mapMarkers');
    }

    public function test_draft_and_archived_listings_are_excluded_from_search()
    {
        $owner = User::factory()->create();
        $property = Property::create([
            'title' => 'Private Unpublished Property',
            'property_code' => 'PROP-TEST-002',
            'owner_user_id' => $owner->id,
        ]);

        $property->listings()->create([
            'purpose' => 'For Rent',
            'price' => 25000,
            'status' => 'draft',
            'visibility' => 'public',
            'owner_actor_id' => $owner->id,
        ]);

        $response = $this->get('/search?q=Unpublished');

        $response->assertStatus(200);
        $response->assertDontSee('Private Unpublished Property');
        $response->assertSee('No Matching Properties Found');
    }

    public function test_keyword_and_location_filtering()
    {
        $owner = User::factory()->create();
        $prop1 = Property::create([
            'title' => 'Serene Villa in Old Airport',
            'property_code' => 'PROP-OLD-01',
            'owner_user_id' => $owner->id,
        ]);
        $prop1->listings()->create([
            'purpose' => 'For Sale',
            'price' => 45000000,
            'status' => 'published',
            'visibility' => 'public',
            'owner_actor_id' => $owner->id,
            'published_at' => now(),
        ]);

        $prop2 = Property::create([
            'title' => 'Commercial Store in Mercato',
            'property_code' => 'PROP-MER-02',
            'owner_user_id' => $owner->id,
        ]);
        $prop2->listings()->create([
            'purpose' => 'For Rent',
            'price' => 50000,
            'status' => 'published',
            'visibility' => 'public',
            'owner_actor_id' => $owner->id,
            'published_at' => now(),
        ]);

        $response = $this->get('/search?q=Old+Airport');
        $response->assertStatus(200);
        $response->assertSee('Serene Villa in Old Airport');
        $response->assertDontSee('Commercial Store in Mercato');
    }

    public function test_combined_filters_work_together()
    {
        $owner = User::factory()->create();
        
        // Property 1: Matches all filters
        $p1 = Property::create([
            'title' => 'Matching Penthouse',
            'property_code' => 'PROP-MATCH-01',
            'owner_user_id' => $owner->id,
            'bedrooms' => 4,
            'bathrooms' => 3,
            'building_area' => 250,
            'status' => 'verified',
        ]);
        $p1->listings()->create([
            'purpose' => 'For Sale',
            'price' => 20000000,
            'status' => 'published',
            'visibility' => 'public',
            'owner_actor_id' => $owner->id,
            'published_at' => now(),
        ]);

        // Property 2: Wrong purpose
        $p2 = Property::create([
            'title' => 'Wrong Purpose Villa',
            'property_code' => 'PROP-MATCH-02',
            'owner_user_id' => $owner->id,
            'bedrooms' => 4,
            'building_area' => 300,
        ]);
        $p2->listings()->create([
            'purpose' => 'For Rent',
            'price' => 50000,
            'status' => 'published',
            'visibility' => 'public',
            'owner_actor_id' => $owner->id,
            'published_at' => now(),
        ]);

        // Property 3: Insufficient bedrooms
        $p3 = Property::create([
            'title' => 'Small Studio For Sale',
            'property_code' => 'PROP-MATCH-03',
            'owner_user_id' => $owner->id,
            'bedrooms' => 1,
            'building_area' => 50,
        ]);
        $p3->listings()->create([
            'purpose' => 'For Sale',
            'price' => 5000000,
            'status' => 'published',
            'visibility' => 'public',
            'owner_actor_id' => $owner->id,
            'published_at' => now(),
        ]);

        $response = $this->get('/search?purpose=For+Sale&bedrooms=3&min_price=10000000&max_price=30000000');
        $response->assertStatus(200);
        $response->assertSee('Matching Penthouse');
        $response->assertDontSee('Wrong Purpose Villa');
        $response->assertDontSee('Small Studio For Sale');
    }

    public function test_price_sorting_order()
    {
        $owner = User::factory()->create();

        $cheap = Property::create([
            'title' => 'Budget Studio',
            'property_code' => 'SORT-CHEAP',
            'owner_user_id' => $owner->id,
        ]);
        $cheap->listings()->create([
            'purpose' => 'For Rent',
            'price' => 8000,
            'status' => 'published',
            'visibility' => 'public',
            'owner_actor_id' => $owner->id,
            'published_at' => now(),
        ]);

        $expensive = Property::create([
            'title' => 'Luxury Mansion',
            'property_code' => 'SORT-EXPENSIVE',
            'owner_user_id' => $owner->id,
        ]);
        $expensive->listings()->create([
            'purpose' => 'For Rent',
            'price' => 120000,
            'status' => 'published',
            'visibility' => 'public',
            'owner_actor_id' => $owner->id,
            'published_at' => now(),
        ]);

        // Price Ascending
        $responseAsc = $this->get('/search?sort=price_asc');
        $responseAsc->assertStatus(200);
        $listingsAsc = $responseAsc->viewData('listings')->items();
        $this->assertEquals(8000, $listingsAsc[0]->price);

        // Price Descending
        $responseDesc = $this->get('/search?sort=price_desc');
        $responseDesc->assertStatus(200);
        $listingsDesc = $responseDesc->viewData('listings')->items();
        $this->assertEquals(120000, $listingsDesc[0]->price);
    }

    public function test_view_mode_switching_grid_list_map()
    {
        $responseGrid = $this->get('/search?view=grid');
        $responseGrid->assertStatus(200);
        $this->assertEquals('grid', $responseGrid->viewData('viewMode'));

        $responseList = $this->get('/search?view=list');
        $responseList->assertStatus(200);
        $this->assertEquals('list', $responseList->viewData('viewMode'));

        $responseMap = $this->get('/search?view=map');
        $responseMap->assertStatus(200);
        $this->assertEquals('map', $responseMap->viewData('viewMode'));
    }

    public function test_verified_only_filter()
    {
        $owner = User::factory()->create();

        $verifiedProp = Property::create([
            'title' => 'Verified Official Home',
            'property_code' => 'PROP-VER-01',
            'owner_user_id' => $owner->id,
            'status' => 'verified',
        ]);
        $verifiedProp->listings()->create([
            'purpose' => 'For Sale',
            'price' => 20000000,
            'status' => 'published',
            'visibility' => 'public',
            'owner_actor_id' => $owner->id,
            'published_at' => now(),
        ]);

        $unverifiedProp = Property::create([
            'title' => 'Pending Review Home',
            'property_code' => 'PROP-UNVER-02',
            'owner_user_id' => $owner->id,
            'status' => 'available',
        ]);
        $unverifiedProp->listings()->create([
            'purpose' => 'For Sale',
            'price' => 20000000,
            'status' => 'published',
            'visibility' => 'public',
            'owner_actor_id' => $owner->id,
            'published_at' => now(),
        ]);

        $response = $this->get('/search?verified=1');
        $response->assertStatus(200);
        $response->assertSee('Verified Official Home');
        $response->assertDontSee('Pending Review Home');
    }

    public function test_amenities_filter()
    {
        $owner = User::factory()->create();
        $generator = Amenity::firstOrCreate(
            ['code' => 'backup_generator'],
            ['name_en' => 'Backup Generator', 'category' => 'Utilities', 'status' => 'active']
        );

        $propWithGen = Property::create([
            'title' => 'Estate With Power Generator',
            'property_code' => 'PROP-AMN-01',
            'owner_user_id' => $owner->id,
        ]);
        $propWithGen->amenities()->attach($generator->id);
        $propWithGen->listings()->create([
            'purpose' => 'For Sale',
            'price' => 30000000,
            'status' => 'published',
            'visibility' => 'public',
            'owner_actor_id' => $owner->id,
            'published_at' => now(),
        ]);

        $propWithout = Property::create([
            'title' => 'Standard Compound No Generator',
            'property_code' => 'PROP-AMN-02',
            'owner_user_id' => $owner->id,
        ]);
        $propWithout->listings()->create([
            'purpose' => 'For Sale',
            'price' => 30000000,
            'status' => 'published',
            'visibility' => 'public',
            'owner_actor_id' => $owner->id,
            'published_at' => now(),
        ]);

        $response = $this->get('/search?amenities[]=backup_generator');
        $response->assertStatus(200);
        $response->assertSee('Estate With Power Generator');
        $response->assertDontSee('Standard Compound No Generator');
    }

    public function test_invalid_filter_values_are_handled_safely()
    {
        // Negative prices, absurd bed counts, SQL injection attempts
        $response = $this->get('/search?min_price=-5000&max_price=abc&bedrooms=9999&q=%27+OR+1%3D1--');
        $response->assertStatus(200);
        // Does not throw an error and shows empty state or clean results
        $this->assertNotNull($response->viewData('listings'));
    }
}
