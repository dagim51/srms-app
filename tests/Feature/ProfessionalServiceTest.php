<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfessionalServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_professional_services_workflow()
    {
        $provider = User::factory()->create();
        $customer = User::factory()->create();

        // 1. Create Profile
        $res = $this->actingAs($provider)->postJson('/api/v1/services/profiles', [
            'business_name' => 'Top Cleaners',
            'category' => 'cleaning',
        ]);
        $res->assertStatus(201);
        $profileId = $res->json('data.id');

        // 2. Create Listing
        $res = $this->actingAs($provider)->postJson("/api/v1/services/profiles/{$profileId}/listings", [
            'title' => 'Deep Cleaning',
            'price' => 150.00,
            'price_unit' => 'fixed',
        ]);
        $res->assertStatus(201);
        $listingId = $res->json('data.id');

        // 3. Unauthorized user cannot create listing for this profile
        $res = $this->actingAs($customer)->postJson("/api/v1/services/profiles/{$profileId}/listings", [
            'title' => 'Fake Service',
            'price' => 10,
            'price_unit' => 'fixed',
        ]);
        $res->assertStatus(403);

        // 4. Customer Requests Service
        $res = $this->actingAs($customer)->postJson("/api/v1/services/listings/{$listingId}/requests", [
            'requirements' => 'Please clean the carpets.',
        ]);
        $res->assertStatus(201);
        $requestId = $res->json('data.id');

        $this->assertEquals('pending', $res->json('data.status'));

        // 5. Provider accepts the request
        $res = $this->actingAs($provider)->patchJson("/api/v1/services/requests/{$requestId}/status", [
            'status' => 'accepted',
        ]);
        $res->assertStatus(200);
        $this->assertEquals('accepted', $res->json('data.status'));

        // 6. Customer cannot complete it
        $res = $this->actingAs($customer)->patchJson("/api/v1/services/requests/{$requestId}/status", [
            'status' => 'completed',
        ]);
        $res->assertStatus(403);
    }
}
