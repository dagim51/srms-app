<?php

namespace Tests\Feature;

use App\Models\Accommodation;
use App\Models\AccommodationAvailability;
use App\Models\Property;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccommodationAvailabilityTest extends TestCase
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
            'title' => 'Test Property',
            'property_code' => 'TEST-AVAIL-001',
            'owner_user_id' => $this->host->id,
            'status' => 'verified',
        ]);

        $this->accommodation = Accommodation::create([
            'property_id' => $this->property->id,
            'host_id' => $this->host->id,
            'max_guests' => 2,
        ]);
    }

    public function test_host_can_block_availability_dates()
    {
        $response = $this->actingAs($this->host)->postJson('/api/v1/accommodation-availabilities', [
            'accommodation_id' => $this->accommodation->id,
            'start_date' => Carbon::now()->addDays(5)->format('Y-m-d'),
            'end_date' => Carbon::now()->addDays(10)->format('Y-m-d'),
            'reason' => 'Maintenance',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.type', 'blocked');

        $this->assertDatabaseHas('accommodation_availabilities', [
            'accommodation_id' => $this->accommodation->id,
            'reason' => 'Maintenance',
        ]);
    }

    public function test_cannot_create_overlapping_blocks()
    {
        AccommodationAvailability::create([
            'accommodation_id' => $this->accommodation->id,
            'start_date' => Carbon::now()->addDays(5)->format('Y-m-d'),
            'end_date' => Carbon::now()->addDays(10)->format('Y-m-d'),
            'type' => 'blocked',
        ]);

        $response = $this->actingAs($this->host)->postJson('/api/v1/accommodation-availabilities', [
            'accommodation_id' => $this->accommodation->id,
            'start_date' => Carbon::now()->addDays(8)->format('Y-m-d'),
            'end_date' => Carbon::now()->addDays(12)->format('Y-m-d'),
        ]);

        $response->assertStatus(409); // Conflict
    }

    public function test_non_host_cannot_block_dates()
    {
        $otherUser = User::factory()->create();

        $response = $this->actingAs($otherUser)->postJson('/api/v1/accommodation-availabilities', [
            'accommodation_id' => $this->accommodation->id,
            'start_date' => Carbon::now()->addDays(5)->format('Y-m-d'),
            'end_date' => Carbon::now()->addDays(10)->format('Y-m-d'),
        ]);

        $response->assertStatus(403);
    }
}
