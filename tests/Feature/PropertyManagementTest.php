<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Property;

class PropertyManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_property_management_workflows()
    {
        $user = User::factory()->create();
        $property = Property::create([
            'property_code' => 'PROP-PM-123',
            'title' => 'Maintenance House',
            'status' => 'active'
        ]);

        // 1. Maintenance Ticket Workflow
        $ticketRes = $this->actingAs($user)->postJson('/api/v1/pm/tickets', [
            'property_id' => $property->id,
            'title' => 'Leaking Faucet',
            'description' => 'Water is leaking in the kitchen',
            'priority' => 'high'
        ]);
        $ticketRes->assertStatus(201);
        $ticketId = $ticketRes->json('data.id');

        $this->actingAs($user)->patchJson("/api/v1/pm/tickets/{$ticketId}/status", [
            'status' => 'in_progress'
        ])->assertStatus(200);

        $this->assertDatabaseHas('maintenance_tickets', [
            'id' => $ticketId,
            'status' => 'in_progress'
        ]);

        // 2. Cleaning Task Workflow
        $cleaningRes = $this->actingAs($user)->postJson('/api/v1/pm/cleaning-tasks', [
            'property_id' => $property->id,
            'notes' => 'Deep clean before next guest'
        ]);
        $cleaningRes->assertStatus(201);
        $taskId = $cleaningRes->json('data.id');

        $this->actingAs($user)->patchJson("/api/v1/pm/cleaning-tasks/{$taskId}/status", [
            'status' => 'inspection'
        ])->assertStatus(200);

        $this->assertDatabaseHas('cleaning_tasks', [
            'id' => $taskId,
            'status' => 'inspection'
        ]);
    }
}
