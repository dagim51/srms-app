<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class AnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_reporting_workflows()
    {
        $user = User::factory()->create();

        // 1. Fetch Dashboard
        $dashRes = $this->actingAs($user)->getJson('/api/v1/analytics/dashboard');
        $dashRes->assertStatus(200)
                ->assertJsonStructure(['data' => ['total_properties', 'occupancy_rate', 'total_revenue']]);

        // 2. Create Template
        $tplRes = $this->actingAs($user)->postJson('/api/v1/analytics/templates', [
            'name' => 'Monthly Financials',
            'type' => 'financial',
            'configuration' => ['columns' => ['revenue', 'expenses', 'net_income']]
        ]);
        $tplRes->assertStatus(201);
        $tplId = $tplRes->json('data.id');

        // 3. Generate Report
        $reportRes = $this->actingAs($user)->postJson('/api/v1/analytics/reports', [
            'report_template_id' => $tplId,
            'format' => 'pdf'
        ]);
        $reportRes->assertStatus(201);
        
        $this->assertDatabaseHas('generated_reports', [
            'report_template_id' => $tplId,
            'format' => 'pdf',
            'status' => 'completed'
        ]);
    }
}
