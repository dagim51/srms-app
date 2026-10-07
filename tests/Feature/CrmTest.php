<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\Lead;

class CrmTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $organization;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        
        $this->organization = Organization::create([
            'name' => 'CRM Agency',
            'slug' => 'crm-agency',
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

    public function test_agent_can_create_and_manage_lead()
    {
        // 1. Create Lead
        $response = $this->actingAs($this->user)->postJson('/api/v1/leads', [
            'organization_id' => $this->organization->id,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'source' => 'web',
            'notes' => 'Looking for a 2-bed apartment.',
        ]);
        
        $response->assertStatus(201)
                 ->assertJsonPath('data.name', 'John Doe');
                 
        $leadId = $response->json('data.id');
                 
        $this->assertDatabaseHas('leads', [
            'id' => $leadId,
            'email' => 'john@example.com',
            'status' => 'new',
        ]);
        
        // 2. Add Activity
        $activityResponse = $this->actingAs($this->user)->postJson("/api/v1/leads/{$leadId}/activities", [
            'type' => 'call',
            'description' => 'Called John to discuss requirements.',
        ]);
        
        $activityResponse->assertStatus(201);
        
        $this->assertDatabaseHas('crm_activities', [
            'lead_id' => $leadId,
            'type' => 'call',
        ]);
        
        // 3. Update Status
        $updateResponse = $this->actingAs($this->user)->patchJson("/api/v1/leads/{$leadId}", [
            'status' => 'qualified',
            'assigned_to' => $this->user->id,
        ]);
        
        $updateResponse->assertStatus(200);
        $this->assertDatabaseHas('leads', [
            'id' => $leadId,
            'status' => 'qualified',
        ]);
    }

    public function test_unauthorized_agent_cannot_access_org_leads()
    {
        $otherUser = User::factory()->create();
        
        $response = $this->actingAs($otherUser)->postJson('/api/v1/leads', [
            'organization_id' => $this->organization->id,
            'name' => 'Jane Smith',
        ]);
        
        $response->assertStatus(403);
    }
}
