<?php

namespace Tests\Feature;

use App\Domains\CRM\Models\Commission;
use App\Domains\CRM\Models\CommissionRule;
use App\Domains\CRM\Models\CrmActivity;
use App\Domains\CRM\Models\Lead;
use App\Domains\CRM\Services\CommissionCalculatorService;
use App\Domains\CRM\Services\LeadAssignmentService;
use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class Module7CrmCommissionsTest extends TestCase
{
    use RefreshDatabase;

    protected User $agent;

    protected User $staff;

    protected Organization $org;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agent = User::factory()->create(['name' => 'Kenenisa Bekele', 'email' => 'kenenisa@broker.et']);
        $this->staff = User::factory()->create();

        $this->org = Organization::create([
            'name' => 'Sheger Luxury Properties',
            'slug' => 'sheger-luxury',
        ]);
    }

    public function test_commissions_web_page_renders_live_records_and_filters_by_status(): void
    {
        Commission::create([
            'agent_id' => $this->agent->id,
            'deal_id' => (string) Str::uuid(),
            'deal_type' => 'sale',
            'gross_amount' => 12000000.0,
            'rate_percentage' => 3.0,
            'commission_amount' => 360000.0,
            'currency' => 'ETB',
            'status' => 'paid',
        ]);

        Commission::create([
            'agent_id' => $this->agent->id,
            'deal_id' => (string) Str::uuid(),
            'deal_type' => 'lease',
            'gross_amount' => 150000.0,
            'rate_percentage' => 10.0,
            'commission_amount' => 15000.0,
            'currency' => 'ETB',
            'status' => 'pending',
        ]);

        // Access all commissions
        $response = $this->actingAs($this->staff)->get('/dashboard/crm/commissions');
        $response->assertStatus(200);
        $response->assertSee('Kenenisa Bekele');
        $response->assertSee('360,000 ETB');
        $response->assertSee('15,000 ETB');

        // Filter by status=paid
        $paidResponse = $this->actingAs($this->staff)->get('/dashboard/crm/commissions?status=paid');
        $paidResponse->assertStatus(200);
        $paidResponse->assertSee('360,000 ETB');
        $paidResponse->assertDontSee('15,000 ETB');

        // Filter by status=pending
        $pendingResponse = $this->actingAs($this->staff)->get('/dashboard/crm/commissions?status=pending');
        $pendingResponse->assertStatus(200);
        $pendingResponse->assertSee('15,000 ETB');
        $pendingResponse->assertDontSee('360,000 ETB');
    }

    public function test_tiered_commission_rule_calculation(): void
    {
        // Custom tier rule: 3.5% + 15,000 ETB bonus for sales over 2M ETB
        CommissionRule::create([
            'organization_id' => $this->org->id,
            'rule_type' => 'sale',
            'percentage' => 3.5,
            'fixed_amount' => 15000.0,
            'min_deal_amount' => 2000000,
            'max_deal_amount' => null,
            'is_active' => true,
        ]);

        $service = app(CommissionCalculatorService::class);

        // 4,000,000 ETB deal -> 3.5% (140,000) + 15,000 = 155,000 ETB
        $commission = $service->calculateAndRecord(
            dealId: (string) Str::uuid(),
            dealType: 'sale',
            grossAmount: 4000000.0,
            agentId: $this->agent->id,
            organizationId: $this->org->id,
            notes: 'High volume deal'
        );

        $this->assertEquals(3.5, $commission->rate_percentage);
        $this->assertEquals(155000.0, $commission->commission_amount);
        $this->assertEquals('pending', $commission->status);
    }

    public function test_lead_auto_assignment_to_least_loaded_organization_agent(): void
    {
        $agentA = User::factory()->create(['name' => 'Agent A']);
        $agentB = User::factory()->create(['name' => 'Agent B']);

        OrganizationMember::create([
            'organization_id' => $this->org->id,
            'user_id' => $agentA->id,
            'role' => 'agent',
        ]);

        OrganizationMember::create([
            'organization_id' => $this->org->id,
            'user_id' => $agentB->id,
            'role' => 'agent',
        ]);

        // Agent A already has 2 active leads
        Lead::create([
            'name' => 'Lead 1',
            'organization_id' => $this->org->id,
            'assigned_to' => $agentA->id,
            'status' => 'new',
        ]);
        Lead::create([
            'name' => 'Lead 2',
            'organization_id' => $this->org->id,
            'assigned_to' => $agentA->id,
            'status' => 'qualified',
        ]);

        // New lead should be auto-assigned to Agent B (least loaded)
        $newLead = Lead::create([
            'name' => 'Prospective Buyer',
            'organization_id' => $this->org->id,
            'status' => 'new',
        ]);

        $service = app(LeadAssignmentService::class);
        $assignedAgent = $service->autoAssign($newLead);

        $this->assertNotNull($assignedAgent);
        $this->assertEquals($agentB->id, $assignedAgent->id);
        $this->assertEquals($agentB->id, $newLead->fresh()->assigned_to);
    }

    public function test_crm_web_lead_creation_and_stage_transitions(): void
    {
        // 1. Create Lead via Web POST
        $createResponse = $this->actingAs($this->staff)->post('/dashboard/crm/leads', [
            'name' => 'Sara Haile',
            'email' => 'sara@example.com',
            'phone' => '+251 91 123 4567',
            'source' => 'web',
            'status' => 'new',
            'assigned_to' => $this->agent->id,
            'notes' => 'Interested in 3-bed apartment in Bole.',
        ]);

        $createResponse->assertRedirect('/dashboard/crm/leads');
        $createResponse->assertSessionHas('success');

        $lead = Lead::where('email', 'sara@example.com')->first();
        $this->assertNotNull($lead);
        $this->assertEquals('new', $lead->status);
        $this->assertEquals($this->agent->id, $lead->assigned_to);

        // 2. Advance stage to Qualified
        $stageResponse = $this->actingAs($this->staff)->post("/dashboard/crm/leads/{$lead->id}/stage", [
            'status' => 'qualified',
        ]);
        $stageResponse->assertSessionHas('success');
        $this->assertEquals('qualified', $lead->fresh()->status);

        // 3. Log an activity on the lead
        $activityResponse = $this->actingAs($this->staff)->post("/dashboard/crm/leads/{$lead->id}/activity", [
            'type' => 'call',
            'description' => 'Discussed pricing and arranged viewing next Tuesday.',
        ]);
        $activityResponse->assertSessionHas('success');
        $this->assertDatabaseHas('crm_activities', [
            'lead_id' => $lead->id,
            'type' => 'call',
        ]);

        // 4. Convert lead to client
        $convertResponse = $this->actingAs($this->staff)->post("/dashboard/crm/leads/{$lead->id}/convert");
        $convertResponse->assertSessionHas('success');
        $this->assertEquals('won', $lead->fresh()->status);
        $this->assertDatabaseHas('users', [
            'email' => 'sara@example.com',
            'name' => 'Sara Haile',
        ]);
    }

    public function test_crm_tasks_scheduling_and_completion(): void
    {
        $lead = Lead::create([
            'name' => 'Dawit Yilma',
            'organization_id' => $this->org->id,
            'status' => 'viewing',
        ]);

        // 1. Schedule a task
        $taskResponse = $this->actingAs($this->staff)->post('/dashboard/crm/tasks', [
            'lead_id' => $lead->id,
            'type' => 'viewing',
            'description' => 'Conduct physical walkthrough of Bole Medhanialem penthouse.',
            'scheduled_at' => now()->addDays(2)->format('Y-m-d'),
        ]);

        $taskResponse->assertRedirect('/dashboard/crm/tasks');
        $taskResponse->assertSessionHas('success');

        $activity = CrmActivity::where('lead_id', $lead->id)->first();
        $this->assertNotNull($activity);
        $this->assertNull($activity->completed_at);

        // 2. Complete the task
        $completeResponse = $this->actingAs($this->staff)->post("/dashboard/crm/tasks/{$activity->id}/complete");
        $completeResponse->assertSessionHas('success');
        $this->assertNotNull($activity->fresh()->completed_at);
    }

    public function test_crm_commission_approval_flow(): void
    {
        $commission = Commission::create([
            'agent_id' => $this->agent->id,
            'deal_id' => (string) Str::uuid(),
            'deal_type' => 'sale',
            'gross_amount' => 5000000.0,
            'rate_percentage' => 4.0,
            'commission_amount' => 200000.0,
            'currency' => 'ETB',
            'status' => 'pending',
        ]);

        $approveResponse = $this->actingAs($this->staff)->post("/dashboard/crm/commissions/{$commission->id}/approve");
        $approveResponse->assertSessionHas('success');
        $this->assertEquals('approved', $commission->fresh()->status);
    }

    public function test_crm_pipeline_kanban_view_renders_stages(): void
    {
        Lead::create([
            'name' => 'Pipeline Prospect 1',
            'organization_id' => $this->org->id,
            'status' => 'viewing',
        ]);

        $response = $this->actingAs($this->staff)->get('/dashboard/crm/pipeline');
        $response->assertStatus(200);
        $response->assertSee('Pipeline Prospect 1');
        $response->assertSee('Viewing');
        $response->assertSee('Negotiation');
        $response->assertSee('Offer');
        $response->assertSee('Won');
    }

    public function test_client_profile_and_leads_linking(): void
    {
        // 1. Create client
        $client = User::factory()->create([
            'name' => 'Helen Mesfin',
            'email' => 'helen@example.com',
            'phone' => '+251 93 456 7890',
        ]);

        // 2. Create lead matching client email
        $lead = Lead::create([
            'name' => 'Helen Mesfin',
            'email' => 'helen@example.com',
            'phone' => '+251 93 456 7890',
            'organization_id' => $this->org->id,
            'status' => 'viewing',
        ]);

        // 3. Client profile renders with linked leads
        $profileResponse = $this->actingAs($this->staff)->get("/dashboard/crm/clients/{$client->id}");
        $profileResponse->assertStatus(200);
        $profileResponse->assertSee('Helen Mesfin');
        $profileResponse->assertSee('helen@example.com');
        $profileResponse->assertSee('/dashboard/crm/leads/'.$lead->id);

        // 4. Leads index links to client profile
        $leadsIndexResponse = $this->actingAs($this->staff)->get('/dashboard/crm/leads');
        $leadsIndexResponse->assertStatus(200);
        $leadsIndexResponse->assertSee('/dashboard/crm/clients/'.$client->id);

        // 5. Lead detail view links to client profile
        $leadShowResponse = $this->actingAs($this->staff)->get("/dashboard/crm/leads/{$lead->id}");
        $leadShowResponse->assertStatus(200);
        $leadShowResponse->assertSee('/dashboard/crm/clients/'.$client->id);

        // 6. Register a new client via CRM POST
        $createClientResponse = $this->actingAs($this->staff)->post('/dashboard/crm/clients', [
            'name' => 'Yonas Tadesse',
            'email' => 'yonas@brokerage.et',
            'phone' => '+251 91 765 4321',
        ]);

        $newClient = User::where('email', 'yonas@brokerage.et')->first();
        $this->assertNotNull($newClient);
        $createClientResponse->assertRedirect("/dashboard/crm/clients/{$newClient->id}");
        $createClientResponse->assertSessionHas('success');
    }
}
