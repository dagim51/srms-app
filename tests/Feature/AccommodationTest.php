<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Property;
use App\Models\Accommodation;

class AccommodationTest extends TestCase
{
    use RefreshDatabase;

    protected $host;
    protected $property;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->host = User::factory()->create();
        
        $this->property = Property::create([
            'title' => 'Test Property for Accommodation',
            'property_code' => 'TEST-ACC-001',
            'owner_user_id' => $this->host->id,
            'status' => 'verified',
        ]);
    }

    public function test_host_can_configure_property_as_accommodation()
    {
        $response = $this->actingAs($this->host)->postJson('/api/v1/accommodations', [
            'property_id' => $this->property->id,
            'max_guests' => 4,
            'bedrooms' => 2,
            'bathrooms' => 1.5,
        ]);
        
        $response->assertStatus(201)
                 ->assertJsonPath('data.max_guests', 4)
                 ->assertJsonPath('data.host_id', $this->host->id);
                 
        $this->assertDatabaseHas('accommodations', [
            'property_id' => $this->property->id,
            'max_guests' => 4,
        ]);
    }

    public function test_non_owner_cannot_configure_accommodation()
    {
        $otherUser = User::factory()->create();
        
        $response = $this->actingAs($otherUser)->postJson('/api/v1/accommodations', [
            'property_id' => $this->property->id,
            'max_guests' => 4,
        ]);
        
        $response->assertStatus(403);
    }

    public function test_host_can_update_accommodation_rules()
    {
        $accommodation = Accommodation::create([
            'property_id' => $this->property->id,
            'host_id' => $this->host->id,
            'max_guests' => 2,
        ]);
        
        $response = $this->actingAs($this->host)->putJson("/api/v1/accommodations/{$accommodation->id}", [
            'rules' => 'No smoking inside.',
            'max_guests' => 3,
        ]);
        
        $response->assertStatus(200);
        $this->assertEquals('No smoking inside.', $accommodation->fresh()->rules);
        $this->assertEquals(3, $accommodation->fresh()->max_guests);
    }

    public function test_cannot_create_multiple_accommodations_for_same_property()
    {
        Accommodation::create([
            'property_id' => $this->property->id,
            'host_id' => $this->host->id,
            'max_guests' => 2,
        ]);
        
        $response = $this->actingAs($this->host)->postJson('/api/v1/accommodations', [
            'property_id' => $this->property->id,
            'max_guests' => 4,
        ]);
        
        $response->assertStatus(422); // Validation error (unique)
    }
}
