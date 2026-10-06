<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Property;
use App\Models\Accommodation;
use App\Models\AccommodationPricingRule;

class AccommodationPricingRuleTest extends TestCase
{
    use RefreshDatabase;

    protected $host;
    protected $property;
    protected $accommodation;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->host = User::factory()->create();
        
        $this->property = Property::create([
            'title' => 'Test Property for Pricing',
            'property_code' => 'TEST-PRICE-001',
            'owner_user_id' => $this->host->id,
            'status' => 'verified',
        ]);
        
        $this->accommodation = Accommodation::create([
            'property_id' => $this->property->id,
            'host_id' => $this->host->id,
            'max_guests' => 4,
        ]);
    }

    public function test_host_can_create_pricing_rule()
    {
        $response = $this->actingAs($this->host)->postJson('/api/v1/accommodation-pricing-rules', [
            'accommodation_id' => $this->accommodation->id,
            'type' => 'weekend',
            'price_modifier_type' => 'percentage',
            'price_modifier_value' => 15.00,
            'priority' => 10,
        ]);
        
        $response->assertStatus(201)
                 ->assertJsonPath('data.type', 'weekend')
                 ->assertJsonPath('data.price_modifier_value', 15);
                 
        $this->assertDatabaseHas('accommodation_pricing_rules', [
            'accommodation_id' => $this->accommodation->id,
            'type' => 'weekend',
        ]);
    }

    public function test_pricing_rules_are_returned_in_priority_order()
    {
        AccommodationPricingRule::create([
            'accommodation_id' => $this->accommodation->id,
            'type' => 'seasonal',
            'price_modifier_type' => 'fixed',
            'price_modifier_value' => 50,
            'priority' => 5,
        ]);
        
        AccommodationPricingRule::create([
            'accommodation_id' => $this->accommodation->id,
            'type' => 'weekend',
            'price_modifier_type' => 'percentage',
            'price_modifier_value' => 10,
            'priority' => 20, // Higher priority should be first
        ]);
        
        $response = $this->actingAs($this->host)->getJson("/api/v1/accommodation-pricing-rules?accommodation_id={$this->accommodation->id}");
        
        $response->assertStatus(200);
        
        // Assert priority 20 is first
        $this->assertEquals('weekend', $response->json('data.0.type'));
        $this->assertEquals('seasonal', $response->json('data.1.type'));
    }

    public function test_non_host_cannot_create_pricing_rule()
    {
        $otherUser = User::factory()->create();
        
        $response = $this->actingAs($otherUser)->postJson('/api/v1/accommodation-pricing-rules', [
            'accommodation_id' => $this->accommodation->id,
            'type' => 'special',
            'price_modifier_type' => 'percentage',
            'price_modifier_value' => 20,
        ]);
        
        $response->assertStatus(403);
    }
}
