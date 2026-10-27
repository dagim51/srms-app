<?php

namespace Tests\Feature;

use App\Domains\Development\Models\HandoverRecord;
use App\Jobs\InstallmentBillingJob;
use App\Models\DevelopmentBuilding;
use App\Models\DevelopmentProject;
use App\Models\DevelopmentUnit;
use App\Models\Organization;
use App\Models\PaymentPlan;
use App\Models\PaymentPlanInstallment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConstructionMilestoneInstallmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_milestone_triggers_installment_billing(): void
    {
        $org = Organization::create([
            'name' => 'Apex Developments',
            'slug' => 'apex-developments',
        ]);

        $project = DevelopmentProject::create([
            'organization_id' => $org->id,
            'name' => 'Apex Skyline Tower',
            'status' => 'under_construction',
            'progress_percentage' => 45.0,
        ]);

        $plan = PaymentPlan::create([
            'development_project_id' => $project->id,
            'name' => 'Standard Construction Staged Plan',
        ]);

        // Installment 1: Foundation (20% progress)
        $inst1 = PaymentPlanInstallment::create([
            'payment_plan_id' => $plan->id,
            'name' => 'Foundation Complete',
            'percentage' => 20.0,
            'due_days_after_reservation' => 30,
        ]);

        // Installment 2: Structure (40% progress)
        $inst2 = PaymentPlanInstallment::create([
            'payment_plan_id' => $plan->id,
            'name' => 'Structure Complete',
            'percentage' => 40.0,
            'due_days_after_reservation' => 60,
        ]);

        // Installment 3: Finishing (80% progress)
        $inst3 = PaymentPlanInstallment::create([
            'payment_plan_id' => $plan->id,
            'name' => 'Finishing Complete',
            'percentage' => 80.0,
            'due_days_after_reservation' => 120,
        ]);

        $buyer = User::factory()->create();

        $building = DevelopmentBuilding::create([
            'development_project_id' => $project->id,
            'name' => 'Building 1',
        ]);

        $unit = DevelopmentUnit::create([
            'development_building_id' => $building->id,
            'unit_number' => 'APT-101',
            'price' => 5000000.0,
            'status' => 'reserved',
        ]);

        HandoverRecord::create([
            'unit_id' => $unit->id,
            'buyer_id' => $buyer->id,
            'status' => 'pending',
        ]);

        // Run the installment billing job for 45% milestone progress
        $job = new InstallmentBillingJob($project->id, 45.0);
        $count = $job->handle();

        // Expect 2 invoices created (Foundation 20% and Structure 40%), but not Finishing 80%
        $this->assertEquals(2, $count);

        $this->assertDatabaseHas('invoices', [
            'user_id' => $buyer->id,
            'amount' => 1000000.0, // 20% of 5,000,000
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('invoices', [
            'user_id' => $buyer->id,
            'amount' => 2000000.0, // 40% of 5,000,000
            'status' => 'pending',
        ]);

        // Running it again should be idempotent (no duplicate invoices created)
        $count2 = (new InstallmentBillingJob($project->id, 45.0))->handle();
        $this->assertEquals(0, $count2);
    }
}
