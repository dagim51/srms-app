<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function test_user_can_create_organization()
    {
        $response = $this->actingAs($this->user)->postJson('/api/v1/organizations', [
            'name' => 'Test Company',
            'slug' => 'test-company',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Test Company');

        $this->assertDatabaseHas('organizations', [
            'slug' => 'test-company',
            'created_by' => $this->user->id,
        ]);

        // Assert user was automatically attached as a member
        $this->assertDatabaseHas('organization_members', [
            'user_id' => $this->user->id,
            'status' => 'active',
        ]);
    }

    public function test_organization_member_can_add_other_agents()
    {
        $organization = Organization::create([
            'name' => 'Existing Company',
            'slug' => 'existing-company',
            'status' => 'active',
            'created_by' => $this->user->id,
        ]);

        OrganizationMember::create([
            'organization_id' => $organization->id,
            'user_id' => $this->user->id,
            'status' => 'active',
            'created_by' => $this->user->id,
        ]);

        $newAgent = User::factory()->create();

        $response = $this->actingAs($this->user)->postJson("/api/v1/organizations/{$organization->id}/members", [
            'user_id' => $newAgent->id,
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('organization_members', [
            'organization_id' => $organization->id,
            'user_id' => $newAgent->id,
        ]);
    }

    public function test_non_members_cannot_add_agents()
    {
        $organization = Organization::create([
            'name' => 'Other Company',
            'slug' => 'other-company',
            'status' => 'active',
            'created_by' => User::factory()->create()->id, // Different owner
        ]);

        $newAgent = User::factory()->create();

        // $this->user is NOT a member of $organization
        $response = $this->actingAs($this->user)->postJson("/api/v1/organizations/{$organization->id}/members", [
            'user_id' => $newAgent->id,
        ]);

        $response->assertStatus(403);
    }
}
