<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityTest extends TestCase
{
    use RefreshDatabase;

    public function test_community_facility_booking_workflow()
    {
        $user = User::factory()->create();

        // 1. Create Community
        $commRes = $this->actingAs($user)->postJson('/api/v1/communities', [
            'name' => 'Palm Grove Estate',
            'description' => 'A lovely community',
            'hoa_rules' => 'No loud music after 10 PM',
        ]);
        $commRes->assertStatus(201);
        $commId = $commRes->json('data.id');

        // 2. Create Facility
        $facRes = $this->actingAs($user)->postJson("/api/v1/communities/{$commId}/facilities", [
            'name' => 'Main Pool',
            'capacity' => 20,
        ]);
        $facRes->assertStatus(201);
        $facId = $facRes->json('data.id');

        // 3. Book Facility
        $bookRes = $this->actingAs($user)->postJson("/api/v1/facilities/{$facId}/bookings", [
            'start_time' => '2026-10-10 10:00:00',
            'end_time' => '2026-10-10 12:00:00',
        ]);
        $bookRes->assertStatus(201);
        $bookId = $bookRes->json('data.id');

        $this->assertDatabaseHas('communities', ['id' => $commId]);
        $this->assertDatabaseHas('shared_facilities', ['id' => $facId]);
        $this->assertDatabaseHas('facility_bookings', [
            'id' => $bookId,
            'status' => 'pending',
        ]);
    }
}
