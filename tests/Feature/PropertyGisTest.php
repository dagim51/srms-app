<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PropertyGisTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_coordinates_can_be_stored()
    {
        $owner = \App\Models\User::factory()->create();
        $token = \Illuminate\Support\Str::random(40);
        \App\Models\ApiToken::create(['user_id' => $owner->id, 'name' => 'test', 'token' => hash('sha256', $token)]);

        $response = $this->withToken($token)->postJson('/api/v1/properties', [
            'title' => 'GIS Property',
            'latitude' => 8.9806, // Addis Ababa
            'longitude' => 38.7578,
        ]);

        $response->assertStatus(201);
        $this->assertEquals(8.9806, $response->json('data.latitude'));
        $this->assertEquals(38.7578, $response->json('data.longitude'));
    }

    public function test_invalid_coordinates_are_rejected()
    {
        $owner = \App\Models\User::factory()->create();
        $token = \Illuminate\Support\Str::random(40);
        \App\Models\ApiToken::create(['user_id' => $owner->id, 'name' => 'test', 'token' => hash('sha256', $token)]);

        $response = $this->withToken($token)->postJson('/api/v1/properties', [
            'title' => 'Invalid GIS Property',
            'latitude' => 95.0, // Invalid lat (>90)
            'longitude' => 200.0, // Invalid lon (>180)
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['latitude', 'longitude']);
    }

    public function test_radius_search_returns_correct_records()
    {
        $owner = \App\Models\User::factory()->create();
        $token = \Illuminate\Support\Str::random(40);
        \App\Models\ApiToken::create(['user_id' => $owner->id, 'name' => 'test', 'token' => hash('sha256', $token)]);

        // Center: Addis Ababa (Bole)
        \App\Models\Property::create([
            'title' => 'Bole Property',
            'property_code' => 'GIS-001',
            'owner_user_id' => $owner->id,
            'latitude' => 8.9806,
            'longitude' => 38.7578,
        ]);

        // Far: Hawassa (~275km away)
        \App\Models\Property::create([
            'title' => 'Hawassa Property',
            'property_code' => 'GIS-002',
            'owner_user_id' => $owner->id,
            'latitude' => 7.0504,
            'longitude' => 38.4682,
        ]);

        // Search within 50km of Addis Ababa (8.98, 38.75)
        $response = $this->withToken($token)->getJson('/api/v1/properties/search/radius?latitude=8.9800&longitude=38.7500&radius=50');
        
        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('Bole Property', $response->json('data.0.title'));

        // Search within 300km (should find both)
        $response2 = $this->withToken($token)->getJson('/api/v1/properties/search/radius?latitude=8.9800&longitude=38.7500&radius=300');
        $response2->assertStatus(200);
        $this->assertCount(2, $response2->json('data'));
    }
}
