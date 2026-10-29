<?php

namespace Tests\Feature;

use App\Domains\Development\Models\DevelopmentBuilding;
use App\Domains\Development\Models\DevelopmentProject;
use App\Domains\Development\Models\DevelopmentUnit;
use App\Domains\Development\Models\HandoverRecord;
use App\Domains\Development\Models\PaymentPlan;
use App\Domains\Development\Models\PaymentPlanInstallment;
use App\Domains\Development\Models\SnaggingItem;
use App\Domains\Identity\Models\User;
use App\Domains\Property\Models\Property;
use App\Domains\Rental\Models\Lease;
use App\Domains\Rental\Models\TenantProfile;
use App\Domains\Rental\Services\LeaseAgreementPdfService;
use App\Domains\Transaction\Models\Invoice;
use App\Models\City;
use App\Models\Country;
use App\Models\Organization;
use App\Models\Region;
use App\Models\RentSchedule;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase4Holes3And4Test extends TestCase
{
    use RefreshDatabase;

    protected User $landlord;

    protected User $tenant;

    protected User $engineer;

    protected Property $property;

    protected function setUp(): void
    {
        parent::setUp();

        $this->landlord = User::factory()->create(['name' => 'Dawit Landlord']);
        $this->tenant = User::factory()->create(['name' => 'Tigist Tenant']);
        $this->engineer = User::factory()->create(['name' => 'Eng. Ermias Certifier']);

        $country = Country::create(['name_en' => 'Ethiopia', 'code' => 'ET', 'phone_code' => '251', 'is_active' => true]);
        $region = Region::create(['country_id' => $country->id, 'name_en' => 'Addis Ababa', 'code' => 'AA', 'is_active' => true]);
        $city = City::create(['region_id' => $region->id, 'name_en' => 'Addis Ababa', 'code' => 'ADD', 'is_active' => true]);

        $this->property = Property::create([
            'property_code' => 'PROP-'.uniqid(),
            'owner_user_id' => $this->landlord->id,
            'title' => 'Kazanchis Executive Apartment',
            'description' => 'Modern 2-bedroom residence near UNECA',
            'status' => 'verified',
            'bedrooms' => 2,
            'bathrooms' => 2,
            'building_area' => 110,
        ]);
    }

    /**
     * Test Hole 3: Construction milestone certification dispatches event and generates installment invoices.
     */
    public function test_construction_milestone_certification_triggers_installment_billing(): void
    {
        $org = Organization::create([
            'name' => 'Entoto Grand Real Estate',
            'slug' => 'entoto-grand',
        ]);

        $project = DevelopmentProject::create([
            'organization_id' => $org->id,
            'name' => 'Entoto Luxury Heights',
            'status' => 'under_construction',
            'progress_percentage' => 10.0,
        ]);

        $plan = PaymentPlan::create([
            'development_project_id' => $project->id,
            'name' => 'Standard Off-Plan 4-Stage Plan',
        ]);

        PaymentPlanInstallment::create([
            'payment_plan_id' => $plan->id,
            'name' => 'Foundation Pouring',
            'percentage' => 20.0,
            'due_days_after_reservation' => 30,
        ]);

        PaymentPlanInstallment::create([
            'payment_plan_id' => $plan->id,
            'name' => 'Structural Slab Level 5',
            'percentage' => 40.0,
            'due_days_after_reservation' => 60,
        ]);

        PaymentPlanInstallment::create([
            'payment_plan_id' => $plan->id,
            'name' => 'Interior Finishing',
            'percentage' => 80.0,
            'due_days_after_reservation' => 120,
        ]);

        $building = DevelopmentBuilding::create([
            'development_project_id' => $project->id,
            'name' => 'Tower Alpha',
        ]);

        $buyer = User::factory()->create(['name' => 'Kiros Off-Plan Buyer']);

        $unit = DevelopmentUnit::create([
            'development_building_id' => $building->id,
            'unit_number' => 'TA-502',
            'price' => 6000000.0,
            'status' => 'reserved',
        ]);

        HandoverRecord::create([
            'unit_id' => $unit->id,
            'buyer_id' => $buyer->id,
            'status' => 'pending',
        ]);

        // Engineer certifies milestone reaching 45% progress
        $project->certifyMilestone(
            'Structural Slab Level 5 Complete',
            45.0,
            $this->engineer,
            'https://srms.et/inspections/report-45-pct.pdf'
        );

        $this->assertEquals(45.0, $project->fresh()->progress_percentage);

        // Expect 2 invoices created (Foundation 20% and Structural Slab 40%), but not Finishing 80%
        $invoices = Invoice::where('user_id', $buyer->id)->get();
        $this->assertCount(2, $invoices);

        $this->assertDatabaseHas('invoices', [
            'user_id' => $buyer->id,
            'amount' => 1200000.0, // 20% of 6,000,000
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('invoices', [
            'user_id' => $buyer->id,
            'amount' => 2400000.0, // 40% of 6,000,000
            'status' => 'pending',
        ]);
    }

    /**
     * Test Hole 3: Snagging defect tracking lifecycle and handover guard.
     */
    public function test_snagging_defect_lifecycle_and_handover_guard(): void
    {
        $org = Organization::create([
            'name' => 'Summit Towers PLC',
            'slug' => 'summit-towers',
        ]);

        $project = DevelopmentProject::create([
            'organization_id' => $org->id,
            'name' => 'Summit Vista',
            'status' => 'completed',
            'progress_percentage' => 100.0,
        ]);

        $building = DevelopmentBuilding::create([
            'development_project_id' => $project->id,
            'name' => 'Block B',
        ]);

        $buyer = User::factory()->create(['name' => 'Selam Buyer']);

        $unit = DevelopmentUnit::create([
            'development_building_id' => $building->id,
            'unit_number' => 'B-304',
            'price' => 4500000.0,
            'status' => 'sold',
            'owner_user_id' => $buyer->id,
        ]);

        $handover = HandoverRecord::create([
            'unit_id' => $unit->id,
            'buyer_id' => $buyer->id,
            'status' => 'pending',
        ]);

        // 1. Initial state: No snags, can complete
        $this->assertFalse($handover->hasPendingSnags());
        $this->assertTrue($handover->canCompleteHandover());

        // 2. Buyer inspects and logs defect
        $snag = SnaggingItem::create([
            'unit_id' => $unit->id,
            'handover_record_id' => $handover->id,
            'buyer_id' => $buyer->id,
            'inspector_id' => $this->engineer->id,
            'room_area' => 'Master Bathroom',
            'description' => 'Water heater pressure valve leaking and hairline floor tile fissure',
            'severity' => 'major',
            'status' => 'open',
            'photo_urls' => ['https://srms.et/snags/bath-leak.jpg'],
        ]);

        // 3. Now has pending snags -> handover must be blocked
        $this->assertTrue($handover->fresh()->hasPendingSnags());
        $this->assertFalse($handover->fresh()->canCompleteHandover());

        try {
            $handover->completeHandover($buyer);
            $this->fail('Expected DomainException was not thrown when completing handover with open snags.');
        } catch (DomainException $e) {
            $this->assertStringContainsString('unresolved defect snagging items remain', $e->getMessage());
        }

        // 4. Contractor rectifies the defect
        $snag->markRectified('Pressure valve replaced with certified brass fitting and cracked tile re-laid.');
        $this->assertEquals('rectified', $snag->fresh()->status);
        $this->assertNotNull($snag->fresh()->rectified_at);

        // Rectified defects still require sign-off verification before handover is permitted
        $this->assertTrue($handover->fresh()->hasPendingSnags());
        $this->assertFalse($handover->fresh()->canCompleteHandover());

        // 5. Buyer conducts re-inspection and signs off
        $snag->verifySignOff($buyer, 'Verified leak-free and tile replaced satisfactorily.');
        $this->assertEquals('verified', $snag->fresh()->status);
        $this->assertNotNull($snag->fresh()->verified_at);

        // 6. Snags are resolved; handover can now proceed cleanly
        $this->assertFalse($handover->fresh()->hasPendingSnags());
        $this->assertTrue($handover->fresh()->canCompleteHandover());

        $handover->completeHandover($buyer, ['meter_reading_electricity' => '12498 kWh']);
        $this->assertEquals('completed', $handover->fresh()->status);
        $this->assertNotNull($handover->fresh()->keys_released_at);
        $this->assertStringContainsString('handover-cert-', $handover->fresh()->signed_document_url);
    }

    /**
     * Test Hole 4: Overdue rent penalty batch command applies 5% late fee after 5-day grace period.
     */
    public function test_overdue_rent_penalty_command(): void
    {
        $tenantProfile = TenantProfile::create([
            'user_id' => $this->tenant->id,
            'employment_status' => 'Employed',
            'annual_income' => 600000.0,
        ]);

        $lease = Lease::create([
            'property_id' => $this->property->id,
            'tenant_profile_id' => $tenantProfile->id,
            'start_date' => now()->subMonths(3)->toDateString(),
            'end_date' => now()->addMonths(9)->toDateString(),
            'rent_amount' => 20000.0,
            'deposit_amount' => 40000.0,
            'status' => 'active',
        ]);

        // Schedule 1: Overdue past 5-day grace period (due 10 days ago)
        $overdueSchedule = RentSchedule::create([
            'lease_id' => $lease->id,
            'due_date' => now()->subDays(10)->toDateString(),
            'amount' => 20000.0,
            'status' => 'unpaid',
        ]);

        $overdueInvoice = Invoice::create([
            'user_id' => $this->tenant->id,
            'amount' => 20000.0,
            'due_date' => now()->subDays(10)->toDateString(),
            'status' => 'unpaid',
            'notes' => "Monthly Rent for Lease #{$lease->id} (Schedule #{$overdueSchedule->id})",
        ]);

        // Schedule 2: Within 5-day grace period (due 2 days ago) - should NOT be penalized
        $graceInvoice = Invoice::create([
            'user_id' => $this->tenant->id,
            'amount' => 20000.0,
            'due_date' => now()->subDays(2)->toDateString(),
            'status' => 'unpaid',
            'notes' => "Monthly Rent for Lease #{$lease->id} - Current Month",
        ]);

        // Execute penalty command
        $this->artisan('srms:apply-rent-penalties', ['--grace-days' => 5, '--penalty-pct' => 5])
            ->expectsOutputToContain('Applied overdue penalties to 1 invoices.')
            ->assertSuccessful();

        // Overdue invoice should have 5% added (20,000 + 1,000 = 21,000 ETB) and marked overdue
        $overdueInvoice->refresh();
        $this->assertEquals(21000.0, (float) $overdueInvoice->amount);
        $this->assertEquals('overdue', $overdueInvoice->status);
        $this->assertStringContainsString('Late penalty applied: +1000 ETB (5%)', $overdueInvoice->notes);

        // Associated RentSchedule must be marked overdue
        $this->assertEquals('overdue', $overdueSchedule->fresh()->status);

        // Grace invoice should NOT be touched
        $graceInvoice->refresh();
        $this->assertEquals(20000.0, (float) $graceInvoice->amount);
        $this->assertEquals('unpaid', $graceInvoice->status);

        // Idempotency: re-running should not re-penalize
        $this->artisan('srms:apply-rent-penalties', ['--grace-days' => 5, '--penalty-pct' => 5])
            ->expectsOutputToContain('Applied overdue penalties to 0 invoices.')
            ->assertSuccessful();
    }

    /**
     * Test Hole 4: Lease agreement PDF service, non-repudiation SHA-256 digest, and dual-party signing.
     */
    public function test_lease_agreement_pdf_service_and_dual_digital_signing(): void
    {
        $tenantProfile = TenantProfile::create([
            'user_id' => $this->tenant->id,
            'employment_status' => 'Self-Employed',
            'annual_income' => 950000.0,
        ]);

        $lease = Lease::create([
            'property_id' => $this->property->id,
            'tenant_profile_id' => $tenantProfile->id,
            'start_date' => '2026-11-01',
            'end_date' => '2027-10-31',
            'rent_amount' => 35000.0,
            'deposit_amount' => 70000.0,
            'status' => 'draft',
        ]);

        // 1. PDF Service compiles bilingual agreement data & cryptographic digest
        $pdfService = new LeaseAgreementPdfService;
        $agreementData = $pdfService->generateAgreementData($lease);

        $this->assertNotEmpty($agreementData['digest']);
        $this->assertEquals(64, strlen($agreementData['digest'])); // Valid SHA-256 string
        $this->assertEquals('Dawit Landlord', $agreementData['landlord']['name']);
        $this->assertEquals('Tigist Tenant', $agreementData['tenant']['name']);
        $this->assertEquals(35000.0, $agreementData['terms']['monthly_rent_etb']);
        $this->assertEquals(5, $agreementData['terms']['grace_period_days']);
        $this->assertArrayHasKey('am', $agreementData['clauses_bilingual']);
        $this->assertArrayHasKey('en', $agreementData['clauses_bilingual']);

        $renderedHtml = $pdfService->renderContractHtml($lease);
        $this->assertStringContainsString('Federal Democratic Republic of Ethiopia', $renderedHtml);
        $this->assertStringContainsString($agreementData['digest'], $renderedHtml);

        // Store digest in lease record
        $lease->update(['agreement_digest' => $agreementData['digest']]);

        // 2. Unauthorized user cannot sign
        $stranger = User::factory()->create(['name' => 'Unauthorized Third Party']);
        try {
            $lease->sign($stranger, '10.0.0.1');
            $this->fail('Expected DomainException was not thrown when stranger attempted to sign lease.');
        } catch (DomainException $e) {
            $this->assertStringContainsString('User is not authorized to sign', $e->getMessage());
        }

        // 3. Tenant signs first
        $this->assertFalse($lease->isFullySigned());
        $signedResult = $lease->sign($this->tenant, '197.156.104.12', 'SIG_BASE64_TENANT_DIGEST');
        $this->assertTrue($signedResult);

        $lease->refresh();
        $this->assertNotNull($lease->signed_by_tenant_at);
        $this->assertNull($lease->signed_by_landlord_at);
        $this->assertFalse($lease->isFullySigned());
        $this->assertEquals('draft', $lease->status); // Still draft, awaiting landlord

        $tenantSig = $lease->signatures['tenant'];
        $this->assertEquals($this->tenant->id, $tenantSig['user_id']);
        $this->assertEquals('197.156.104.12', $tenantSig['ip_address']);

        // 4. Landlord countersigns -> triggers automatic lease activation and schedule generation
        $lease->sign($this->landlord, '196.188.241.5', 'SIG_BASE64_LANDLORD_DIGEST');

        $lease->refresh();
        $this->assertNotNull($lease->signed_by_landlord_at);
        $this->assertTrue($lease->isFullySigned());
        $this->assertEquals('active', $lease->status);

        $landlordSig = $lease->signatures['landlord'];
        $this->assertEquals($this->landlord->id, $landlordSig['user_id']);
        $this->assertEquals('196.188.241.5', $landlordSig['ip_address']);

        // 12 monthly rent schedules should have been automatically generated
        $this->assertEquals(12, $lease->schedules()->count());
        $this->assertEquals('unpaid', $lease->schedules()->first()->status);
    }
}
