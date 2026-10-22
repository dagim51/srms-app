<?php

namespace Tests\Feature;

use App\Models\Listing;
use App\Models\Property;
use App\Models\User;
use App\Models\Viewing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ViewingTest extends TestCase
{
    use RefreshDatabase;

    protected $requester;

    protected $agent;

    protected $listing;

    protected function setUp(): void
    {
        parent::setUp();

        $this->requester = User::factory()->create();
        $this->agent = User::factory()->create();

        $property = Property::create([
            'title' => 'Test Property for Viewing',
            'property_code' => 'TEST-VW-001',
            'owner_user_id' => $this->agent->id,
            'status' => 'verified',
        ]);

        $this->listing = Listing::create([
            'property_id' => $property->id,
            'purpose' => 'For Sale',
            'price' => 5000000,
            'owner_actor_id' => $this->agent->id,
            'status' => 'published',
            'visibility' => 'public',
        ]);
    }

    public function test_viewing_requests_work()
    {
        $response = $this->actingAs($this->requester)->postJson('/api/v1/viewings', [
            'listing_id' => $this->listing->id,
            'scheduled_at' => now()->addDays(2)->format('Y-m-d H:i:s'),
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.status', 'requested');

        $this->assertDatabaseHas('viewings', [
            'requester_id' => $this->requester->id,
            'listing_id' => $this->listing->id,
        ]);
    }

    public function test_authorized_agents_can_respond_and_status_transitions_are_validated()
    {
        $viewing = Viewing::create([
            'listing_id' => $this->listing->id,
            'requester_id' => $this->requester->id,
            'agent_id' => $this->agent->id,
            'status' => 'requested',
            'scheduled_at' => now()->addDays(2),
        ]);

        // Agent confirms
        $response = $this->actingAs($this->agent)->putJson("/api/v1/viewings/{$viewing->id}", [
            'status' => 'confirmed',
        ]);

        $response->assertStatus(200);
        $this->assertEquals('confirmed', $viewing->fresh()->status);

        // Requester tries to confirm (unauthorized modification blocked)
        $response2 = $this->actingAs($this->requester)->putJson("/api/v1/viewings/{$viewing->id}", [
            'status' => 'completed',
        ]);

        $response2->assertStatus(403);
    }

    public function test_scheduling_conflicts_are_handled()
    {
        $time = now()->addDays(2)->format('Y-m-d H:i:s');

        Viewing::create([
            'listing_id' => $this->listing->id,
            'requester_id' => User::factory()->create()->id,
            'agent_id' => $this->agent->id,
            'status' => 'confirmed',
            'scheduled_at' => $time,
        ]);

        $response = $this->actingAs($this->requester)->postJson('/api/v1/viewings', [
            'listing_id' => $this->listing->id,
            'scheduled_at' => $time,
        ]);

        $response->assertStatus(409);
    }
}
