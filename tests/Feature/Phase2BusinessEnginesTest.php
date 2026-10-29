<?php

namespace Tests\Feature;

use App\Domains\Booking\Models\AccommodationBooking;
use App\Domains\Development\Models\DevelopmentBuilding;
use App\Domains\Development\Models\DevelopmentProject;
use App\Domains\Development\Models\DevelopmentUnit;
use App\Domains\Development\Models\HandoverRecord;
use App\Domains\Development\Models\PaymentPlan;
use App\Domains\Development\Models\PaymentPlanInstallment;
use App\Domains\Rental\Models\Lease;
use App\Domains\Rental\Models\RentSchedule;
use App\Domains\Rental\Models\TenantProfile;
use App\Domains\Stay\Services\CalendarSyncService;
use App\Domains\Transaction\Models\Invoice;
use App\Models\Accommodation;
use App\Models\AccommodationPricingRule;
use App\Models\Organization;
use App\Models\Property;
use App\Models\PropertyType;
use App\Models\User;
use App\Services\Bookings\BookingManager;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class Phase2BusinessEnginesTest extends TestCase
{
    use RefreshDatabase;

    protected User $host;

    protected User $guest;

    protected Accommodation $accommodation;

    protected Property $property;

    protected function setUp(): void
    {
        parent::setUp();

        $this->host = User::factory()->create(['name' => 'Host User']);
        $this->guest = User::factory()->create(['name' => 'Guest User']);

        $propertyType = PropertyType::firstOrCreate(['code' => 'APT'], ['name_en' => 'Apartment']);
        $this->property = Property::create([
            'title' => 'Kazanchis Executive Suite',
            'property_code' => 'KAZ-'.uniqid(),
            'type_id' => $propertyType->id,
            'status' => 'active',
            'price' => 2000.00,
            'currency' => 'ETB',
        ]);

        $this->accommodation = Accommodation::create([
            'property_id' => $this->property->id,
            'host_id' => $this->host->id,
            'max_guests' => 4,
            'bedrooms' => 2,
            'beds' => 2,
            'bathrooms' => 2,
            'check_in_time' => '14:00',
            'check_out_time' => '11:00',
            'is_active' => true,
            'minimum_stay' => 1,
            'maximum_stay' => 30,
        ]);
    }

    public function test_dynamic_pricing_calculation_and_checkout_integration(): void
    {
        $manager = app(BookingManager::class);

        // Add weekend pricing rule (+20% modifier)
        AccommodationPricingRule::create([
            'accommodation_id' => $this->accommodation->id,
            'name' => 'Weekend Surcharge',
            'type' => 'weekend',
            'price_modifier_type' => 'percentage',
            'price_modifier_value' => 20.0,
            'priority' => 10,
            'is_active' => true,
        ]);

        // A Friday to Monday stay (3 nights, 2 of which are Friday & Saturday weekends)
        $checkIn = Carbon::parse('next friday')->toDateString();
        $checkOut = Carbon::parse('next friday')->addDays(3)->toDateString();

        $pricing = $manager->calculateDetailedPrice($this->accommodation, $checkIn, $checkOut);

        $this->assertEquals(3, $pricing['nights']);
        $this->assertGreaterThan(0, $pricing['stay_subtotal']);
        $this->assertGreaterThan(0, $pricing['cleaning_fee']);
        $this->assertGreaterThan(0, $pricing['service_fee']);
        $this->assertGreaterThan(0, $pricing['tax_amount']);
        $this->assertEquals(
            round($pricing['stay_subtotal'] + $pricing['cleaning_fee'] + $pricing['service_fee'] + $pricing['tax_amount'], 2),
            $pricing['total_price']
        );

        // Verify web checkout page renders price breakdown dynamically
        $response = $this->actingAs($this->guest)->get(route('booking.create', [
            'accommodation' => $this->accommodation->id,
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'guests' => 2,
        ]));

        $response->assertStatus(200);
        $response->assertSee('Price details');
        $response->assertSee('SRMS service fee');
        $response->assertSee('VAT & Taxes');
    }

    public function test_temporary_hold_creation_and_confirmation_flow(): void
    {
        $checkIn = Carbon::now()->addDays(20)->toDateString();
        $checkOut = Carbon::now()->addDays(23)->toDateString();

        // 1. Guest initiates booking request
        $response = $this->actingAs($this->guest)->post(route('booking.store', $this->accommodation), [
            'check_in_date' => $checkIn,
            'check_out_date' => $checkOut,
            'guests' => 2,
        ]);

        $booking = AccommodationBooking::where('accommodation_id', $this->accommodation->id)
            ->where('user_id', $this->guest->id)
            ->first();

        $this->assertNotNull($booking);
        $this->assertEquals('holding', $booking->status);
        $this->assertNotNull($booking->expires_at);
        $this->assertTrue($booking->expires_at->isFuture());

        $response->assertRedirect(route('booking.show', $booking));

        // 2. Guest completes payment and confirms the hold
        $confirmResponse = $this->actingAs($this->guest)->post(route('booking.confirm', $booking));
        $confirmResponse->assertRedirect(route('booking.show', $booking));

        $this->assertEquals('confirmed', $booking->fresh()->status);
    }

    public function test_ical_calendar_export_and_import_fidelity(): void
    {
        $manager = app(BookingManager::class);
        $syncService = app(CalendarSyncService::class);

        $checkIn = Carbon::now()->addDays(30)->toDateString();
        $checkOut = Carbon::now()->addDays(34)->toDateString();

        $booking = $manager->create([
            'accommodation_id' => $this->accommodation->id,
            'check_in_date' => $checkIn,
            'check_out_date' => $checkOut,
            'guests' => 2,
        ], $this->guest->id);

        $this->assertEquals('pending', $booking->status);

        // 1. Export Calendar Feed via HTTP Route
        $response = $this->get(route('accommodation.calendar.ics', $this->accommodation));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/calendar; charset=utf-8');

        $icsBody = $response->getContent();
        $this->assertStringContainsString('BEGIN:VCALENDAR', $icsBody);
        $this->assertStringContainsString('BEGIN:VEVENT', $icsBody);
        $this->assertStringContainsString('END:VCALENDAR', $icsBody);

        // 2. Round-trip Import: Verify parser correctly interprets the exported ICS events
        $parsedEvents = $syncService->parseIcsEvents($icsBody);
        $this->assertNotEmpty($parsedEvents);
        $this->assertEquals($checkIn, $parsedEvents[0]['start_date']);
    }

    public function test_lease_activation_generates_rent_schedules_and_cron_issues_invoices(): void
    {
        $tenantUser = User::factory()->create(['name' => 'Abebe Bikila', 'phone' => '+251911223344']);
        $tenantProfile = TenantProfile::create([
            'user_id' => $tenantUser->id,
            'employment_status' => 'Employed',
            'annual_income' => 360000.00,
        ]);

        $startDate = Carbon::now()->startOfMonth()->toDateString();
        $endDate = Carbon::now()->startOfMonth()->addMonths(5)->endOfMonth()->toDateString(); // 6 months

        $lease = Lease::create([
            'property_id' => $this->property->id,
            'tenant_profile_id' => $tenantProfile->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'rent_amount' => 25000.00,
            'deposit_amount' => 50000.00,
            'status' => 'pending',
        ]);

        // 1. Activate Lease
        $lease->activate();

        $this->assertEquals('active', $lease->status);
        $schedules = RentSchedule::where('lease_id', $lease->id)->get();
        $this->assertCount(6, $schedules, 'Expected 6 monthly rent schedules to be generated.');

        // 2. Run invoice generation command
        $exitCode = Artisan::call('srms:generate-rent-invoices', ['--days' => 15]);
        $this->assertEquals(0, $exitCode);

        // Verify an invoice was created for the upcoming schedule
        $invoice = Invoice::where('user_id', $tenantUser->id)->first();
        $this->assertNotNull($invoice);
        $this->assertEquals(25000.00, (float) $invoice->amount);
        $this->assertStringContainsString("Lease #{$lease->id}", $invoice->notes);
    }

    public function test_construction_milestone_progress_automatically_bills_reserved_unit_buyers(): void
    {
        $buyer = User::factory()->create(['name' => 'Selamawit Desta']);

        $org = Organization::create([
            'name' => 'Varnero Real Estate Developers',
            'slug' => 'varnero-'.uniqid(),
            'status' => 'active',
        ]);

        $project = DevelopmentProject::create([
            'organization_id' => $org->id,
            'name' => 'Bole Medhanealem Heights',
            'status' => 'active',
            'progress_percentage' => 0.0,
        ]);

        $building = DevelopmentBuilding::create([
            'development_project_id' => $project->id,
            'name' => 'Block A',
        ]);

        $unit = DevelopmentUnit::create([
            'development_building_id' => $building->id,
            'unit_number' => 'A-402',
            'price' => 3000000.00, // 3,000,000 ETB
            'status' => 'reserved',
        ]);

        // Link unit to buyer via HandoverRecord
        HandoverRecord::create([
            'unit_id' => $unit->id,
            'buyer_id' => $buyer->id,
            'status' => 'pending',
        ]);

        // Setup payment plan with 2 staged milestones
        $plan = PaymentPlan::create([
            'development_project_id' => $project->id,
            'name' => 'Standard Off-Plan Plan',
        ]);

        $installment1 = PaymentPlanInstallment::create([
            'payment_plan_id' => $plan->id,
            'name' => 'Foundation Stage Completed',
            'percentage' => 20.0, // 20%
            'due_days_after_reservation' => 30,
        ]);

        $installment2 = PaymentPlanInstallment::create([
            'payment_plan_id' => $plan->id,
            'name' => 'Structural Framing Completed',
            'percentage' => 40.0, // 40%
            'due_days_after_reservation' => 60,
        ]);

        // 1. Update project progress to 25% (un infantile surpasses 20% milestone)
        $invoicesCount = $project->updateProgress(25.0);
        $this->assertEquals(1, $invoicesCount);

        // Verify invoice for 20% (600,000 ETB) was generated
        $invoice1 = Invoice::where('user_id', $buyer->id)
            ->where('notes', 'like', '%Foundation Stage Completed%')
            ->first();

        $this->assertNotNull($invoice1);
        $this->assertEquals(600000.00, (float) $invoice1->amount);

        // 2. Re-triggering same or slightly higher progress under next threshold does NOT duplicate
        $invoicesCount2 = $project->updateProgress(30.0);
        $this->assertEquals(0, $invoicesCount2);
        $this->assertEquals(1, Invoice::where('user_id', $buyer->id)->count());

        // 3. Advancing progress to 50% triggers the 40% structural milestone invoice
        $invoicesCount3 = $project->updateProgress(50.0);
        $this->assertEquals(1, $invoicesCount3);

        $invoice2 = Invoice::where('user_id', $buyer->id)
            ->where('notes', 'like', '%Structural Framing Completed%')
            ->first();

        $this->assertNotNull($invoice2);
        $this->assertEquals(1200000.00, (float) $invoice2->amount); // 40% of 3,000,000
    }
}
