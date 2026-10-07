<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\DevelopmentProject;

class DevelopmentTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $organization;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        
        $this->organization = Organization::create([
            'name' => 'Developer Corp',
            'slug' => 'dev-corp',
            'status' => 'active',
            'created_by' => $this->user->id,
        ]);
        
        OrganizationMember::create([
            'organization_id' => $this->organization->id,
            'user_id' => $this->user->id,
            'status' => 'active',
            'created_by' => $this->user->id,
        ]);
    }

    public function test_dev_can_create_project_building_and_unit()
    {
        // 1. Create Project
        $response = $this->actingAs($this->user)->postJson('/api/v1/development/projects', [
            'organization_id' => $this->organization->id,
            'name' => 'Sunset Towers',
            'handover_date' => '2028-01-01',
        ]);
        $response->assertStatus(201);
        $projectId = $response->json('data.id');

        // 2. Add Building
        $bldgResponse = $this->actingAs($this->user)->postJson("/api/v1/development/projects/{$projectId}/buildings", [
            'name' => 'Tower A',
            'total_floors' => 10,
        ]);
        $bldgResponse->assertStatus(201);
        $bldgId = $bldgResponse->json('data.id');

        // 3. Add Unit
        $unitResponse = $this->actingAs($this->user)->postJson("/api/v1/development/buildings/{$bldgId}/units", [
            'floor_name' => 'Ground',
            'unit_number' => 'G-01',
            'status' => 'available',
            'price' => 150000.00
        ]);
        $unitResponse->assertStatus(201);
        
        // 4. Add Payment Plan
        $planResponse = $this->actingAs($this->user)->postJson("/api/v1/development/projects/{$projectId}/payment-plans", [
            'name' => 'Standard Handover Plan',
        ]);
        $planResponse->assertStatus(201);

        $this->assertDatabaseHas('development_projects', ['id' => $projectId, 'name' => 'Sunset Towers']);
        $this->assertDatabaseHas('development_buildings', ['id' => $bldgId, 'name' => 'Tower A']);
        $this->assertDatabaseHas('development_units', ['unit_number' => 'G-01', 'price' => 150000.00]);
        $this->assertDatabaseHas('payment_plans', ['name' => 'Standard Handover Plan']);
    }
}
