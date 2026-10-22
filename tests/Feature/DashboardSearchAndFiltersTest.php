<?php

namespace Tests\Feature;

use App\Models\Accommodation;
use App\Models\City;
use App\Models\Country;
use App\Models\Listing;
use App\Models\Offer;
use App\Models\Property;
use App\Models\PropertyType;
use App\Models\Region;
use App\Models\Reservation;
use App\Models\SubCity;
use App\Models\User;
use App\Models\Viewing;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardSearchAndFiltersTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected User $otherUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['name' => 'Test Host User', 'email' => 'host@srms.et']);
        $this->otherUser = User::factory()->create(['name' => 'Test Buyer User', 'email' => 'buyer@srms.et']);
    }

    public function test_property_search_and_filtering_across_fields_and_relationships()
    {
        $country = Country::create(['code' => 'ET', 'name_en' => 'Ethiopia']);
        $region = Region::create(['country_id' => $country->id, 'name_en' => 'Addis Ababa']);
        $city = City::create(['region_id' => $region->id, 'name_en' => 'Addis Ababa']);
        $subCity = SubCity::create(['city_id' => $city->id, 'name_en' => 'Bole Sub City']);
        $type = PropertyType::create(['name_en' => 'Penthouse Luxury', 'code' => 'PENT', 'status' => 'active']);

        $matchingProperty = Property::create([
            'title' => 'Stunning Bole Penthouse Suite',
            'property_code' => 'PROP-BOL-770',
            'address' => 'Atlas Road 12',
            'owner_user_id' => $this->user->id,
            'status' => 'available',
            'type_id' => $type->id,
            'sub_city_id' => $subCity->id,
        ]);

        $otherProperty = Property::create([
            'title' => 'Rustic Country Villa',
            'property_code' => 'PROP-VIL-101',
            'address' => 'Bishoftu Road 9',
            'owner_user_id' => $this->user->id,
            'status' => 'draft',
        ]);

        // Search by title
        $response = $this->actingAs($this->user)->get('/dashboard/properties?search=Penthouse');
        $response->assertStatus(200)
            ->assertSee('Stunning Bole Penthouse Suite')
            ->assertDontSee('Rustic Country Villa');

        // Search by location relationship (subCity name)
        $response = $this->actingAs($this->user)->get('/dashboard/properties?search=Bole Sub City');
        $response->assertStatus(200)
            ->assertSee('Stunning Bole Penthouse Suite')
            ->assertDontSee('Rustic Country Villa');

        // Filter by property type
        $response = $this->actingAs($this->user)->get('/dashboard/properties?type_id='.$type->id);
        $response->assertStatus(200)
            ->assertSee('Stunning Bole Penthouse Suite')
            ->assertDontSee('Rustic Country Villa');

        // Filter by status
        $response = $this->actingAs($this->user)->get('/dashboard/properties?status=draft');
        $response->assertStatus(200)
            ->assertSee('Rustic Country Villa')
            ->assertDontSee('Stunning Bole Penthouse Suite');

        // Filter empty state
        $response = $this->actingAs($this->user)->get('/dashboard/properties?search=NonExistentKeywordXYZ');
        $response->assertStatus(200)
            ->assertSee('No matching properties found')
            ->assertSee('Clear All Filters');
    }

    public function test_accommodations_search_and_status_filtering()
    {
        $propertyA = Property::create([
            'title' => 'Cozy Bole Studio',
            'property_code' => 'ACC-PROP-01',
            'address' => 'Bole Medhanialem',
            'owner_user_id' => $this->user->id,
            'status' => 'verified',
        ]);

        $propertyB = Property::create([
            'title' => 'Kazanchis Executive Suite',
            'property_code' => 'ACC-PROP-02',
            'address' => 'Kazanchis Core',
            'owner_user_id' => $this->user->id,
            'status' => 'verified',
        ]);

        $accA = Accommodation::create([
            'property_id' => $propertyA->id,
            'host_id' => $this->user->id,
            'max_guests' => 2,
            'bedrooms' => 1,
            'beds' => 1,
            'bathrooms' => 1,
            'is_active' => true,
        ]);

        $accB = Accommodation::create([
            'property_id' => $propertyB->id,
            'host_id' => $this->user->id,
            'max_guests' => 4,
            'bedrooms' => 2,
            'beds' => 2,
            'bathrooms' => 2,
            'is_active' => false,
        ]);

        // Search by property title
        $response = $this->actingAs($this->user)->get('/dashboard/accommodations?search=Studio');
        $response->assertStatus(200)
            ->assertSee('Cozy Bole Studio')
            ->assertDontSee('Kazanchis Executive Suite');

        // Filter by active status
        $response = $this->actingAs($this->user)->get('/dashboard/accommodations?status=active');
        $response->assertStatus(200)
            ->assertSee('Cozy Bole Studio')
            ->assertDontSee('Kazanchis Executive Suite');

        // Filter by inactive status
        $response = $this->actingAs($this->user)->get('/dashboard/accommodations?status=inactive');
        $response->assertStatus(200)
            ->assertSee('Kazanchis Executive Suite')
            ->assertDontSee('Cozy Bole Studio');

        // No match empty state
        $response = $this->actingAs($this->user)->get('/dashboard/accommodations?search=UnknownAccommodation123');
        $response->assertStatus(200)
            ->assertSee('No matching accommodations')
            ->assertSee('Clear All Filters');
    }

    public function test_viewings_search_and_status_filtering()
    {
        $propertyA = Property::create([
            'title' => 'Modern Villa Viewing',
            'property_code' => 'VIEW-PROP-1',
            'address' => 'CMC St 4',
            'owner_user_id' => $this->user->id,
            'status' => 'verified',
        ]);

        $propertyB = Property::create([
            'title' => 'Heritage House Tour',
            'property_code' => 'VIEW-PROP-2',
            'address' => 'Piazza 8',
            'owner_user_id' => $this->user->id,
            'status' => 'verified',
        ]);

        $listingA = Listing::create([
            'property_id' => $propertyA->id,
            'owner_actor_id' => $this->user->id,
            'purpose' => 'sale',
            'status' => 'published',
            'price' => 25000000,
        ]);

        $listingB = Listing::create([
            'property_id' => $propertyB->id,
            'owner_actor_id' => $this->user->id,
            'purpose' => 'rent',
            'status' => 'published',
            'price' => 50000,
        ]);

        $viewingA = Viewing::create([
            'listing_id' => $listingA->id,
            'requester_id' => $this->otherUser->id,
            'agent_id' => $this->user->id,
            'scheduled_at' => Carbon::now()->addDays(2),
            'status' => 'confirmed',
        ]);

        $viewingB = Viewing::create([
            'listing_id' => $listingB->id,
            'requester_id' => $this->otherUser->id,
            'agent_id' => $this->user->id,
            'scheduled_at' => Carbon::now()->addDays(5),
            'status' => 'requested',
        ]);

        // Search by listing title
        $response = $this->actingAs($this->user)->get('/dashboard/viewings?search=Modern Villa');
        $response->assertStatus(200)
            ->assertSee('Modern Villa Viewing')
            ->assertDontSee('Heritage House Tour');

        // Filter by confirmed status
        $response = $this->actingAs($this->user)->get('/dashboard/viewings?status=confirmed');
        $response->assertStatus(200)
            ->assertSee('Modern Villa Viewing')
            ->assertDontSee('Heritage House Tour');

        // No match empty state
        $response = $this->actingAs($this->user)->get('/dashboard/viewings?search=NoMatchedViewingKeyword');
        $response->assertStatus(200)
            ->assertSee('No matching appointments')
            ->assertSee('Clear All Filters');
    }

    public function test_offers_search_and_status_filtering()
    {
        $propertyA = Property::create([
            'title' => 'Atlas Commercial Plaza',
            'property_code' => 'OFF-PROP-1',
            'address' => 'Atlas Square',
            'owner_user_id' => $this->user->id,
            'status' => 'verified',
        ]);

        $propertyB = Property::create([
            'title' => 'Sarbet High-Rise Office',
            'property_code' => 'OFF-PROP-2',
            'address' => 'Sarbet St',
            'owner_user_id' => $this->user->id,
            'status' => 'verified',
        ]);

        $listingA = Listing::create([
            'property_id' => $propertyA->id,
            'owner_actor_id' => $this->user->id,
            'purpose' => 'sale',
            'status' => 'published',
            'price' => 80000000,
        ]);

        $listingB = Listing::create([
            'property_id' => $propertyB->id,
            'owner_actor_id' => $this->user->id,
            'purpose' => 'sale',
            'status' => 'published',
            'price' => 45000000,
        ]);

        $offerA = Offer::create([
            'listing_id' => $listingA->id,
            'buyer_id' => $this->otherUser->id,
            'amount' => 75000000,
            'status' => 'accepted',
        ]);

        $offerB = Offer::create([
            'listing_id' => $listingB->id,
            'buyer_id' => $this->otherUser->id,
            'amount' => 40000000,
            'status' => 'pending',
        ]);

        // Search by property title
        $response = $this->actingAs($this->user)->get('/dashboard/offers?search=Atlas');
        $response->assertStatus(200)
            ->assertSee('Atlas Commercial Plaza')
            ->assertDontSee('Sarbet High-Rise Office');

        // Filter by status accepted
        $response = $this->actingAs($this->user)->get('/dashboard/offers?status=accepted');
        $response->assertStatus(200)
            ->assertSee('Atlas Commercial Plaza')
            ->assertDontSee('Sarbet High-Rise Office');

        // Empty state
        $response = $this->actingAs($this->user)->get('/dashboard/offers?search=NonExistentOffer');
        $response->assertStatus(200)
            ->assertSee('No matching offers')
            ->assertSee('Clear All Filters');
    }

    public function test_reservations_search_and_status_filtering()
    {
        $propertyA = Property::create([
            'title' => 'Waterfront Lake Lodge',
            'property_code' => 'RES-PROP-1',
            'address' => 'Bishoftu Lake',
            'owner_user_id' => $this->otherUser->id,
            'status' => 'verified',
        ]);

        $propertyB = Property::create([
            'title' => 'City View Loft',
            'property_code' => 'RES-PROP-2',
            'address' => 'Kazanchis St',
            'owner_user_id' => $this->otherUser->id,
            'status' => 'verified',
        ]);

        $listingA = Listing::create([
            'property_id' => $propertyA->id,
            'owner_actor_id' => $this->otherUser->id,
            'purpose' => 'rent',
            'status' => 'published',
            'price' => 15000,
        ]);

        $listingB = Listing::create([
            'property_id' => $propertyB->id,
            'owner_actor_id' => $this->otherUser->id,
            'purpose' => 'rent',
            'status' => 'published',
            'price' => 12000,
        ]);

        $reservationA = Reservation::create([
            'listing_id' => $listingA->id,
            'user_id' => $this->user->id,
            'reservation_fee' => 3000,
            'status' => 'confirmed',
        ]);

        $reservationB = Reservation::create([
            'listing_id' => $listingB->id,
            'user_id' => $this->user->id,
            'reservation_fee' => 2000,
            'status' => 'pending',
            'expires_at' => Carbon::now()->addHours(24),
        ]);

        // Search by property title
        $response = $this->actingAs($this->user)->get('/dashboard/reservations?search=Waterfront');
        $response->assertStatus(200)
            ->assertSee('Waterfront Lake Lodge')
            ->assertDontSee('City View Loft');

        // Filter by status confirmed
        $response = $this->actingAs($this->user)->get('/dashboard/reservations?status=confirmed');
        $response->assertStatus(200)
            ->assertSee('Waterfront Lake Lodge')
            ->assertDontSee('City View Loft');

        // Empty state
        $response = $this->actingAs($this->user)->get('/dashboard/reservations?search=UnmatchedStayXYZ');
        $response->assertStatus(200)
            ->assertSee('No matching reservations')
            ->assertSee('Clear All Filters');
    }

    public function test_transactions_deals_search_with_both_search_and_q_keys()
    {
        $responseQ = $this->actingAs($this->user)->get('/dashboard/transactions?q=Bole');
        $responseQ->assertStatus(200)
            ->assertSee('TRX-ADD-8821');

        $responseSearch = $this->actingAs($this->user)->get('/dashboard/transactions?search=Bole');
        $responseSearch->assertStatus(200)
            ->assertSee('TRX-ADD-8821');
    }
}
