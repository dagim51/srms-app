<?php

namespace Tests\Feature;

use App\Domains\Development\Models\ConstructionProgress;
use App\Domains\Development\Models\DevelopmentBuilding;
use App\Domains\Development\Models\DevelopmentProject;
use App\Domains\Development\Models\DevelopmentUnit;
use App\Domains\Development\Models\HandoverRecord;
use App\Domains\Development\Models\PaymentPlan;
use App\Domains\Development\Models\PaymentPlanInstallment;
use App\Models\Organization;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Module6DeveloperSalesTest extends TestCase
{
    use RefreshDatabase;

    protected User $developer;

    protected User $buyer;

    protected User $inspector;

    protected Organization $org;

    protected DevelopmentProject $project;

    protected DevelopmentBuilding $building;

    protected DevelopmentUnit $unit;

    protected function setUp(): void
    {
        parent::setUp();

        $this->developer = User::factory()->create();
        $this->buyer = User::factory()->create(['name' => 'Almaz Ayana', 'email' => 'almaz@example.com']);
        $this->inspector = User::factory()->create(['name' => 'Engineer Haile', 'email' => 'haile@inspector.com']);

        $this->org = Organization::create([
            'name' => 'Oromia Prime Developers',
            'slug' => 'oromia-prime',
        ]);

        $this->project = DevelopmentProject::create([
            'organization_id' => $this->org->id,
            'name' => 'Bole Horizon Residence',
            'status' => 'under_construction',
            'progress_percentage' => 35.0,
        ]);

        $this->building = DevelopmentBuilding::create([
            'development_project_id' => $this->project->id,
            'name' => 'Horizon Block B',
        ]);

        $this->unit = DevelopmentUnit::create([
            'development_building_id' => $this->building->id,
            'unit_number' => 'B-404',
            'floor_name' => '4th Floor',
            'price' => 7500000.0,
            'status' => 'contracted',
        ]);
    }

    public function test_buyers_list_and_detail_renders_live_database_records(): void
    {
        // Link buyer via HandoverRecord
        HandoverRecord::create([
            'unit_id' => $this->unit->id,
            'buyer_id' => $this->buyer->id,
            'status' => 'pending',
            'keys_released_at' => null,
        ]);

        // Access buyers list
        $response = $this->actingAs($this->developer)->get('/dashboard/sales/buyers');
        $response->assertStatus(200);
        $response->assertSee('Almaz Ayana');
        $response->assertSee('almaz@example.com');

        // Access buyer detail
        $detailResponse = $this->actingAs($this->developer)->get("/dashboard/sales/buyers/{$this->buyer->id}");
        $detailResponse->assertStatus(200);
        $detailResponse->assertSee('Almaz Ayana');
        $detailResponse->assertSee('B-404');
        $detailResponse->assertSee('Bole Horizon Residence');
    }

    public function test_contracts_page_renders_unit_and_buyer_information(): void
    {
        HandoverRecord::create([
            'unit_id' => $this->unit->id,
            'buyer_id' => $this->buyer->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->developer)->get('/dashboard/sales/contracts');
        $response->assertStatus(200);
        $response->assertSee('B-404');
        $response->assertSee('Almaz Ayana');
        $response->assertSee('7,500,000 ETB');
    }

    public function test_handovers_page_renders_live_handover_records(): void
    {
        HandoverRecord::create([
            'unit_id' => $this->unit->id,
            'buyer_id' => $this->buyer->id,
            'status' => 'scheduled',
            'keys_released_at' => Carbon::now()->addDays(14),
            'inspection_checklist' => ['plumbing' => true, 'electrical' => true, 'finishes' => false],
        ]);

        $response = $this->actingAs($this->developer)->get('/dashboard/sales/handovers');
        $response->assertStatus(200);
        $response->assertSee('B-404');
        $response->assertSee('Almaz Ayana');
        $response->assertSee('scheduled');
    }

    public function test_construction_progress_milestone_tracking_with_proof_media(): void
    {
        $progress = ConstructionProgress::create([
            'project_id' => $this->project->id,
            'milestone_name' => 'Substructure & Foundation Poured',
            'completion_percentage' => 35.0,
            'verified_at' => Carbon::now(),
            'inspector_id' => $this->inspector->id,
            'media_urls' => [
                'https://srms.et/media/foundation-inspection-1.jpg',
                'https://srms.et/media/foundation-inspection-2.jpg',
            ],
            'notes' => 'Reinforced concrete columns inspected and passed grade 30 test.',
        ]);

        $this->assertDatabaseHas('construction_progress', [
            'id' => $progress->id,
            'milestone_name' => 'Substructure & Foundation Poured',
            'inspector_id' => $this->inspector->id,
        ]);

        $this->assertEquals(35.0, (float) $progress->completion_percentage);
        $this->assertCount(2, $progress->media_urls);
        $this->assertTrue($progress->inspector->is($this->inspector));
    }

    public function test_console_command_audits_milestones(): void
    {
        $plan = PaymentPlan::create([
            'development_project_id' => $this->project->id,
            'name' => 'Milestone Staged Plan',
        ]);

        PaymentPlanInstallment::create([
            'payment_plan_id' => $plan->id,
            'name' => 'Foundation Stage',
            'percentage' => 20.0,
            'due_days_after_reservation' => 30,
        ]);

        $this->artisan('srms:check-milestones')
            ->expectsOutputToContain('Auditing development project milestones...')
            ->expectsOutputToContain('Bole Horizon Residence')
            ->assertExitCode(0);
    }
}
