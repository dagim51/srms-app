<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Property;
use App\Models\TenantProfile;

class RentalTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_rental_flow()
    {
        $tenant = User::factory()->create();
        $property = Property::create([
            'property_code' => 'PROP-1234',
            'title' => 'Test Apartment',
            'status' => 'active'
        ]);

        // 1. Create Profile
        $profileResponse = $this->actingAs($tenant)->postJson('/api/v1/rental/profile', [
            'employment_status' => 'Employed',
            'annual_income' => 60000,
        ]);
        $profileResponse->assertStatus(201);
        $profileId = $profileResponse->json('data.id');

        // 2. Submit Application
        $appResponse = $this->actingAs($tenant)->postJson('/api/v1/rental/applications', [
            'property_id' => $property->id,
            'proposed_rent' => 1200,
            'message' => 'I love this place!',
        ]);
        $appResponse->assertStatus(201);
        $appId = $appResponse->json('data.id');

        // 3. Approve & Generate Lease (simulate property manager acting)
        $manager = User::factory()->create();
        $leaseResponse = $this->actingAs($manager)->postJson("/api/v1/rental/applications/{$appId}/approve", [
            'start_date' => '2026-11-01',
            'end_date' => '2027-11-01',
            'rent_amount' => 1200,
            'deposit_amount' => 1200,
        ]);
        
        $leaseResponse->assertStatus(201);
        
        $this->assertDatabaseHas('rental_applications', [
            'id' => $appId,
            'status' => 'approved'
        ]);

        $this->assertDatabaseHas('leases', [
            'property_id' => $property->id,
            'rent_amount' => 1200
        ]);

        // Should generate 12 months of rent schedules
        $leaseId = $leaseResponse->json('data.id');
        $this->assertDatabaseCount('rent_schedules', 12);
        
        $this->assertDatabaseHas('rent_schedules', [
            'lease_id' => $leaseId,
            'due_date' => '2026-11-01',
            'amount' => 1200
        ]);
    }
}
