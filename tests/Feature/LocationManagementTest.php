<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Country;
use App\Models\Property;
use App\Models\PropertyCategory;
use App\Models\PropertyType;
use App\Models\Region;
use App\Models\SubCity;
use App\Models\User;
use App\Models\Woreda;
use App\Models\Zone;
use Database\Seeders\AuthorizationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocationManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Country $country;

    protected Region $region;

    protected City $city;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AuthorizationSeeder::class);
        $this->user = User::factory()->create();
        $this->user->assignRole('Administrator');

        $this->country = Country::create([
            'code' => 'ET',
            'name_en' => 'Ethiopia',
            'name_am' => 'ኢትዮጵያ',
            'name_om' => 'Itoophiyaa',
            'status' => 'active',
        ]);

        $this->region = Region::create([
            'country_id' => $this->country->id,
            'name_en' => 'Addis Ababa',
            'name_am' => 'አዲስ አበባ',
            'name_om' => 'Finfinnee',
            'status' => 'active',
        ]);

        $this->city = City::create([
            'region_id' => $this->region->id,
            'name_en' => 'Addis Ababa City',
            'name_am' => 'አዲስ አበባ ከተማ',
            'status' => 'active',
        ]);
    }

    public function test_locations_dashboard_renders_with_hierarchy_data(): void
    {
        $response = $this->actingAs($this->user)->get('/dashboard/locations');
        $response->assertStatus(200);
        $response->assertViewHas(['totalSubCities', 'totalZones', 'totalWoredas', 'activeUnits', 'subCities', 'zones', 'woredas']);
        $response->assertSee('Sub-Cities');
        $response->assertSee('Regional Zones');
        $response->assertSee('Woredas');
    }

    public function test_sub_city_crud_and_status_toggle(): void
    {
        // 1. Create Sub-City
        $response = $this->actingAs($this->user)->post('/dashboard/locations/sub-cities', [
            'city_id' => $this->city->id,
            'name_en' => 'Bole',
            'name_am' => 'ቦሌ',
            'name_om' => 'Bolee',
            'status' => 'active',
        ]);
        $response->assertRedirect('/dashboard/locations?tab=subcities');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('sub_cities', [
            'city_id' => $this->city->id,
            'name_en' => 'Bole',
            'name_am' => 'ቦሌ',
            'status' => 'active',
        ]);

        $subCity = SubCity::where('name_en', 'Bole')->first();
        $this->assertNotNull($subCity);

        // 2. Update Sub-City
        $updateResponse = $this->actingAs($this->user)->put("/dashboard/locations/sub-cities/{$subCity->id}", [
            'city_id' => $this->city->id,
            'name_en' => 'Bole Sub-City Premium',
            'name_am' => 'ቦሌ ክ/ከተማ',
            'name_om' => 'Kutaa Magaalaa Bolee',
            'status' => 'active',
        ]);
        $updateResponse->assertRedirect('/dashboard/locations?tab=subcities');
        $this->assertDatabaseHas('sub_cities', [
            'id' => $subCity->id,
            'name_en' => 'Bole Sub-City Premium',
        ]);

        // 3. Toggle Status to Inactive
        $toggleResponse = $this->actingAs($this->user)->patch("/dashboard/locations/toggle/subcity/{$subCity->id}");
        $toggleResponse->assertRedirect('/dashboard/locations?tab=subcities');
        $this->assertEquals('inactive', $subCity->fresh()->status);

        // 4. Soft Delete Sub-City
        $deleteResponse = $this->actingAs($this->user)->delete("/dashboard/locations/subcity/{$subCity->id}");
        $deleteResponse->assertRedirect('/dashboard/locations?tab=subcities');
        $this->assertSoftDeleted('sub_cities', ['id' => $subCity->id]);
    }

    public function test_zone_crud_and_status_toggle(): void
    {
        // 1. Create Zone
        $response = $this->actingAs($this->user)->post('/dashboard/locations/zones', [
            'region_id' => $this->region->id,
            'name_en' => 'East Shewa',
            'name_am' => 'ምስራቅ ሸዋ',
            'name_om' => 'Shawaa Bahaa',
            'status' => 'active',
        ]);
        $response->assertRedirect('/dashboard/locations?tab=zones');
        $response->assertSessionHas('success');

        $zone = Zone::where('name_en', 'East Shewa')->first();
        $this->assertNotNull($zone);

        // 2. Update Zone
        $updateResponse = $this->actingAs($this->user)->put("/dashboard/locations/zones/{$zone->id}", [
            'region_id' => $this->region->id,
            'name_en' => 'East Shewa Zone',
            'name_am' => 'ምስራቅ ሸዋ ዞን',
            'status' => 'active',
        ]);
        $updateResponse->assertRedirect('/dashboard/locations?tab=zones');
        $this->assertEquals('East Shewa Zone', $zone->fresh()->name_en);

        // 3. Toggle Status
        $this->actingAs($this->user)->patch("/dashboard/locations/toggle/zone/{$zone->id}");
        $this->assertEquals('inactive', $zone->fresh()->status);

        // 4. Soft Delete
        $this->actingAs($this->user)->delete("/dashboard/locations/zone/{$zone->id}");
        $this->assertSoftDeleted('zones', ['id' => $zone->id]);
    }

    public function test_woreda_crud_and_status_toggle(): void
    {
        $subCity = SubCity::create([
            'city_id' => $this->city->id,
            'name_en' => 'Yeka',
            'status' => 'active',
        ]);

        // 1. Create Woreda under Sub-City
        $response = $this->actingAs($this->user)->post('/dashboard/locations/woredas', [
            'parent_type' => 'sub_city',
            'sub_city_id' => $subCity->id,
            'name_en' => 'Woreda 08',
            'name_am' => 'ወረዳ 08',
            'status' => 'active',
        ]);
        $response->assertRedirect('/dashboard/locations?tab=woredas');
        $response->assertSessionHas('success');

        $woreda = Woreda::where('name_en', 'Woreda 08')->first();
        $this->assertNotNull($woreda);
        $this->assertEquals($subCity->id, $woreda->sub_city_id);

        // 2. Update Woreda
        $this->actingAs($this->user)->put("/dashboard/locations/woredas/{$woreda->id}", [
            'parent_type' => 'sub_city',
            'sub_city_id' => $subCity->id,
            'name_en' => 'Woreda 08 - Megenagna',
            'name_am' => 'ወረዳ 08 - መገናኛ',
            'status' => 'active',
        ]);
        $this->assertEquals('Woreda 08 - Megenagna', $woreda->fresh()->name_en);

        // 3. Toggle Status
        $this->actingAs($this->user)->patch("/dashboard/locations/toggle/woreda/{$woreda->id}");
        $this->assertEquals('inactive', $woreda->fresh()->status);

        // 4. Soft Delete
        $this->actingAs($this->user)->delete("/dashboard/locations/woreda/{$woreda->id}");
        $this->assertSoftDeleted('woredas', ['id' => $woreda->id]);
    }

    public function test_cascade_api_returns_structured_hierarchy(): void
    {
        $subCity = SubCity::create([
            'city_id' => $this->city->id,
            'name_en' => 'Kirkos',
            'status' => 'active',
        ]);

        $woreda = Woreda::create([
            'sub_city_id' => $subCity->id,
            'name_en' => 'Woreda 02',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->user)->getJson('/dashboard/locations/api/cascade');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'regions' => [
                '*' => ['id', 'name_en', 'name_am', 'name_om', 'cities'],
            ],
            'sub_cities' => [
                '*' => ['id', 'type', 'city_id', 'region_id', 'name_en'],
            ],
            'zones',
            'woredas' => [
                '*' => ['id', 'zone_id', 'sub_city_id', 'name_en'],
            ],
        ]);
    }

    public function test_property_creation_and_update_with_location_hierarchy(): void
    {
        $category = PropertyCategory::create([
            'code' => 'residential_loc',
            'name_en' => 'Residential',
            'status' => 'active',
        ]);

        $type = PropertyType::create([
            'code' => 'apartment_loc',
            'name_en' => 'Luxury Apartment',
            'category_id' => $category->id,
            'status' => 'active',
        ]);

        $subCity = SubCity::create([
            'city_id' => $this->city->id,
            'name_en' => 'Bole',
            'name_am' => 'ቦሌ',
            'status' => 'active',
        ]);

        $woreda = Woreda::create([
            'sub_city_id' => $subCity->id,
            'name_en' => 'Woreda 03',
            'name_am' => 'ወረዳ 03',
            'status' => 'active',
        ]);

        // 1. Create Property with Location Hierarchy
        $response = $this->actingAs($this->user)->post('/dashboard/properties', [
            'title' => 'Diplomatic Sky Villa',
            'category_id' => $category->id,
            'type_id' => $type->id,
            'status' => 'available',
            'region_id' => $this->region->id,
            'sub_city_id' => $subCity->id,
            'woreda_id' => $woreda->id,
            'address' => 'Atlas Hotel Area, Namibia St.',
            'bedrooms' => 3,
            'bathrooms' => 2,
            'area' => 185,
        ]);

        $response->assertRedirect('/dashboard/properties');
        $response->assertSessionHas('success');

        $property = Property::where('title', 'Diplomatic Sky Villa')->first();
        $this->assertNotNull($property);
        $this->assertEquals($this->region->id, $property->region_id);
        $this->assertEquals($subCity->id, $property->sub_city_id);
        $this->assertEquals($woreda->id, $property->woreda_id);
        $this->assertEquals('Bole Sub-City, Woreda 03', $property->location_summary);

        // 2. Update Property Location Hierarchy
        $newWoreda = Woreda::create([
            'sub_city_id' => $subCity->id,
            'name_en' => 'Woreda 05',
            'status' => 'active',
        ]);

        $updateResponse = $this->actingAs($this->user)->put("/dashboard/properties/{$property->id}", [
            'title' => 'Diplomatic Sky Villa Renovation',
            'region_id' => $this->region->id,
            'sub_city_id' => $subCity->id,
            'woreda_id' => $newWoreda->id,
            'status' => 'available',
        ]);

        $updateResponse->assertRedirect('/dashboard/properties');
        $this->assertEquals($newWoreda->id, $property->fresh()->woreda_id);
        $this->assertEquals('Bole Sub-City, Woreda 05', $property->fresh()->location_summary);
    }

    public function test_properties_index_shows_location_summary_badge(): void
    {
        $subCity = SubCity::create([
            'city_id' => $this->city->id,
            'name_en' => 'Nifas Silk-Lafto',
            'status' => 'active',
        ]);

        $woreda = Woreda::create([
            'sub_city_id' => $subCity->id,
            'name_en' => 'Woreda 01',
            'status' => 'active',
        ]);

        $property = Property::create([
            'property_code' => 'PROP-2026-LOC01',
            'title' => 'Lafto Green Residence',
            'status' => 'available',
            'region_id' => $this->region->id,
            'sub_city_id' => $subCity->id,
            'woreda_id' => $woreda->id,
            'address' => 'Venezia Roundabout',
            'bedrooms' => 2,
            'bathrooms' => 1,
            'building_area' => 95,
        ]);

        $response = $this->actingAs($this->user)->get('/dashboard/properties');
        $response->assertStatus(200);
        $response->assertSee('Nifas Silk-Lafto Sub-City, Woreda 01');
    }
}
