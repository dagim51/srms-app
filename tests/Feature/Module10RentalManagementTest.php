<?php

namespace Tests\Feature;

use App\Jobs\CheckOverdueRentJob;
use App\Models\Invoice;
use App\Models\Lease;
use App\Models\Property;
use App\Models\RentSchedule;
use App\Models\TenantProfile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Module10RentalManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $staff;

    protected User $tenantUser;

    protected Property $property;

    protected TenantProfile $tenantProfile;

    protected Lease $lease;

    protected function setUp(): void
    {
        parent::setUp();

        $this->staff = User::factory()->create();
        $this->tenantUser = User::factory()->create(['name' => 'Senait Gidey', 'phone' => '+251911998877']);

        $this->property = Property::create([
            'title' => 'Old Airport Modern Apartment #3B',
            'property_code' => 'OAP-APT-003',
            'owner_user_id' => $this->staff->id,
            'status' => 'verified',
        ]);

        $this->tenantProfile = TenantProfile::create([
            'user_id' => $this->tenantUser->id,
            'monthly_income' => 75000.0,
            'status' => 'active',
        ]);

        $this->lease = Lease::create([
            'tenant_profile_id' => $this->tenantProfile->id,
            'property_id' => $this->property->id,
            'landlord_id' => $this->staff->id,
            'start_date' => Carbon::now()->subMonths(2)->toDateString(),
            'end_date' => Carbon::now()->addMonths(10)->toDateString(),
            'rent_amount' => 35000.0,
            'deposit_amount' => 70000.0,
            'status' => 'active',
        ]);
    }

    public function test_rental_web_controller_renders_tenants_leases_and_payments(): void
    {
        RentSchedule::create([
            'lease_id' => $this->lease->id,
            'due_date' => Carbon::now()->subDays(5)->toDateString(),
            'amount' => 35000.0,
            'status' => 'overdue',
        ]);

        // Verify tenants list
        $tenantsResponse = $this->actingAs($this->staff)->get('/dashboard/rental/tenants');
        $tenantsResponse->assertStatus(200);
        $tenantsResponse->assertSee('Senait Gidey');
        $tenantsResponse->assertSee('Old Airport Modern Apartment #3B');
        $tenantsResponse->assertSee('35,000 ETB');

        // Verify leases list
        $leasesResponse = $this->actingAs($this->staff)->get('/dashboard/rental/leases');
        $leasesResponse->assertStatus(200);
        $leasesResponse->assertSee('Senait Gidey');
        $leasesResponse->assertSee('Old Airport Modern Apartment #3B');
        $leasesResponse->assertSee('35,000 ETB');

        // Verify payments list
        $paymentsResponse = $this->actingAs($this->staff)->get('/dashboard/rental/payments');
        $paymentsResponse->assertStatus(200);
        $paymentsResponse->assertSee('Senait Gidey');
        $paymentsResponse->assertSee('35,000 ETB');
    }

    public function test_automated_rent_invoice_generation_command(): void
    {
        // Unpaid schedule due in 3 days (within standard 7 days horizon)
        $dueDate = Carbon::now()->addDays(3)->toDateString();

        $schedule = RentSchedule::create([
            'lease_id' => $this->lease->id,
            'due_date' => $dueDate,
            'amount' => 35000.0,
            'status' => 'unpaid',
        ]);

        $this->artisan('srms:generate-rent-invoices')
            ->expectsOutputToContain('Scanning for unpaid rent schedules')
            ->expectsOutputToContain('Generated 1 rent invoice(s).')
            ->assertExitCode(0);

        $this->assertDatabaseHas('invoices', [
            'user_id' => $this->tenantUser->id,
            'amount' => 35000.0,
            'due_date' => $dueDate,
            'status' => 'sent',
        ]);

        // Running again is idempotent (does not duplicate invoice)
        $this->artisan('srms:generate-rent-invoices')
            ->expectsOutputToContain('Generated 0 rent invoice(s).')
            ->assertExitCode(0);
    }

    public function test_overdue_rent_job_flags_past_due_schedules(): void
    {
        // Unpaid schedule due 2 days ago
        $pastDueDate = Carbon::now()->subDays(2)->toDateString();

        $schedule = RentSchedule::create([
            'lease_id' => $this->lease->id,
            'due_date' => $pastDueDate,
            'amount' => 35000.0,
            'status' => 'unpaid',
        ]);

        $job = new CheckOverdueRentJob;
        $overdueCount = $job->handle();

        $this->assertEquals(1, $overdueCount);
        $this->assertEquals('overdue', $schedule->fresh()->status);
    }
}
