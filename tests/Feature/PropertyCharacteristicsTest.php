<?php

namespace Tests\Feature;

use App\Models\Amenity;
use App\Models\ApiToken;
use App\Models\Property;
use App\Models\PropertyCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PropertyCharacteristicsTest extends TestCase
{
    use RefreshDatabase;

    public function test_amenities_can_be_attached_and_detached()
    {
        $owner = User::factory()->create();
        $token = Str::random(40);
        ApiToken::create(['user_id' => $owner->id, 'name' => 'test', 'token' => hash('sha256', $token)]);

        $property = Property::create([
            'title' => 'Test Property',
            'property_code' => 'TEST-001',
            'owner_user_id' => $owner->id,
            'status' => 'draft',
        ]);

        $amenity = Amenity::create([
            'category' => 'indoor',
            'code' => 'WIFI',
            'name_en' => 'WiFi',
            'status' => 'active',
        ]);

        // Attach
        $response = $this->withToken($token)->postJson("/api/v1/properties/{$property->id}/amenities", [
            'amenity_id' => $amenity->id,
            'value' => 'High Speed',
        ]);
        $response->assertStatus(200);

        $this->assertDatabaseHas('property_amenities', [
            'property_id' => $property->id,
            'amenity_id' => $amenity->id,
            'value' => 'High Speed',
        ]);

        // Detach
        $response = $this->withToken($token)->deleteJson("/api/v1/properties/{$property->id}/amenities/{$amenity->id}");
        $response->assertStatus(200);

        $this->assertDatabaseMissing('property_amenities', [
            'property_id' => $property->id,
            'amenity_id' => $amenity->id,
        ]);

        // Property still exists
        $this->assertDatabaseHas('properties', ['id' => $property->id]);
    }

    public function test_property_filtering_by_characteristics()
    {
        $owner = User::factory()->create();
        $token = Str::random(40);
        ApiToken::create(['user_id' => $owner->id, 'name' => 'test', 'token' => hash('sha256', $token)]);

        $category = PropertyCategory::create([
            'code' => 'RES',
            'name_en' => 'Residential',
            'status' => 'active',
        ]);

        $amenity = Amenity::create([
            'category' => 'outdoor',
            'code' => 'POOL',
            'name_en' => 'Swimming Pool',
            'status' => 'active',
        ]);

        $property1 = Property::create([
            'title' => 'House with Pool',
            'property_code' => 'PROP-1',
            'owner_user_id' => $owner->id,
            'category_id' => $category->id,
            'bedrooms' => 3,
            'status' => 'verified',
        ]);
        $property1->amenities()->attach($amenity->id);

        $property2 = Property::create([
            'title' => 'Apartment',
            'property_code' => 'PROP-2',
            'owner_user_id' => $owner->id,
            'category_id' => $category->id,
            'bedrooms' => 2,
            'status' => 'verified',
        ]);

        // Filter by category
        $response = $this->withToken($token)->getJson("/api/v1/properties?category_id={$category->id}");
        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));

        // Filter by bedrooms
        $response = $this->withToken($token)->getJson('/api/v1/properties?bedrooms=3');
        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('House with Pool', $response->json('data.0.title'));

        // Filter by amenity
        $response = $this->withToken($token)->getJson("/api/v1/properties?amenity_id={$amenity->id}");
        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('House with Pool', $response->json('data.0.title'));
    }
}
