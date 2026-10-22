<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketingTest extends TestCase
{
    use RefreshDatabase;

    public function test_marketing_campaign_and_lead_workflow()
    {
        $user = User::factory()->create();
        $org = Organization::create([
            'name' => 'Test Org',
            'slug' => 'test-org',
        ]);

        // 1. Create Campaign
        $campRes = $this->actingAs($user)->postJson('/api/v1/marketing/campaigns', [
            'name' => 'Summer Promo',
            'channel' => 'social',
            'start_date' => '2026-06-01',
        ]);
        $campRes->assertStatus(201);
        $campId = $campRes->json('data.id');

        // 2. Create Lead
        $leadRes = $this->actingAs($user)->postJson('/api/v1/marketing/leads', [
            'marketing_campaign_id' => $campId,
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'source' => 'Facebook Ad',
        ]);
        $leadRes->assertStatus(201);
        $leadId = $leadRes->json('data.id');

        // 3. Convert Lead
        $convertRes = $this->actingAs($user)->postJson("/api/v1/marketing/leads/{$leadId}/convert");
        $convertRes->assertStatus(200);

        $this->assertDatabaseHas('leads', [
            'id' => $leadId,
            'status' => 'won',
        ]);
    }
}
