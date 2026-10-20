<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Property;
use App\Models\Lease;
use App\Models\TenantProfile;
use App\Models\Accommodation;
use App\Models\AccommodationBooking;
use App\Models\Dispute;
use App\Models\Payment;
use Illuminate\Support\Facades\Gate;

class MultiTenantSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $owner;
    protected User $tenant;
    protected User $unrelated;
    protected Property $property;
    protected Lease $lease;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\AuthorizationSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('Administrator');

        $this->owner = User::factory()->create();
        $this->tenant = User::factory()->create();
        $this->unrelated = User::factory()->create();

        $this->property = Property::create([
            'property_code' => 'PROP-SEC-001',
            'title' => 'Security Test Property',
            'owner_user_id' => $this->owner->id,
            'status' => 'available',
        ]);

        $tenantProfile = TenantProfile::create([
            'user_id' => $this->tenant->id,
            'employment_status' => 'Employed',
        ]);

        $this->lease = Lease::create([
            'property_id' => $this->property->id,
            'tenant_profile_id' => $tenantProfile->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addYear()->toDateString(),
            'rent_amount' => 25000,
            'status' => 'active',
        ]);
    }

    public function test_lease_policy_authorizes_correct_actors_and_rejects_unrelated_users(): void
    {
        // Admin can view
        $this->assertTrue(Gate::forUser($this->admin)->allows('view', $this->lease));

        // Tenant can view
        $this->assertTrue(Gate::forUser($this->tenant)->allows('view', $this->lease));

        // Owner can view
        $this->assertTrue(Gate::forUser($this->owner)->allows('view', $this->lease));

        // Unrelated user cannot view
        $this->assertFalse(Gate::forUser($this->unrelated)->allows('view', $this->lease));
    }

    public function test_accommodation_booking_policy_authorizes_guest_and_host(): void
    {
        $accommodation = Accommodation::create([
            'property_id' => $this->property->id,
            'host_id' => $this->owner->id,
            'title' => 'Penthouse Stay',
            'base_rate' => 5000,
            'is_active' => true,
        ]);

        $booking = AccommodationBooking::create([
            'accommodation_id' => $accommodation->id,
            'user_id' => $this->tenant->id,
            'check_in_date' => now()->addDays(5)->toDateString(),
            'check_out_date' => now()->addDays(10)->toDateString(),
            'status' => 'pending',
            'total_price' => 25000,
        ]);

        // Guest can view
        $this->assertTrue(Gate::forUser($this->tenant)->allows('view', $booking));

        // Host/Owner can view
        $this->assertTrue(Gate::forUser($this->owner)->allows('view', $booking));

        // Admin can view
        $this->assertTrue(Gate::forUser($this->admin)->allows('view', $booking));

        // Unrelated cannot view
        $this->assertFalse(Gate::forUser($this->unrelated)->allows('view', $booking));
    }

    public function test_dispute_policy_authorizes_parties_and_rejects_third_parties(): void
    {
        $dispute = Dispute::create([
            'complainant_id' => $this->tenant->id,
            'respondent_id' => $this->owner->id,
            'disputable_type' => Property::class,
            'disputable_id' => $this->property->id,
            'type' => 'deposit_dispute',
            'subject' => 'Deposit Refund Dispute',
            'description' => 'Dispute description for testing purposes',
            'amount_in_dispute' => 5000,
            'status' => 'open',
        ]);

        // Complainant can view
        $this->assertTrue(Gate::forUser($this->tenant)->allows('view', $dispute));

        // Respondent can view
        $this->assertTrue(Gate::forUser($this->owner)->allows('view', $dispute));

        // Admin can view
        $this->assertTrue(Gate::forUser($this->admin)->allows('view', $dispute));

        // Unrelated cannot view
        $this->assertFalse(Gate::forUser($this->unrelated)->allows('view', $dispute));
    }

    public function test_payment_policy_authorizes_payer_and_admin(): void
    {
        $payment = Payment::create([
            'user_id' => $this->tenant->id,
            'payable_type' => Property::class,
            'payable_id' => $this->property->id,
            'amount' => 25000,
            'currency' => 'ETB',
            'payment_method' => 'telebirr',
            'transaction_reference' => 'TRX-TEST-001',
            'status' => 'completed',
        ]);

        // Payer can view
        $this->assertTrue(Gate::forUser($this->tenant)->allows('view', $payment));

        // Admin can view
        $this->assertTrue(Gate::forUser($this->admin)->allows('view', $payment));

        // Unrelated cannot view
        $this->assertFalse(Gate::forUser($this->unrelated)->allows('view', $payment));
    }

    public function test_idempotency_middleware_prevents_duplicate_mutating_requests(): void
    {
        $idempotencyKey = 'unique-key-' . uniqid();

        // Simulate request through the middleware
        $middleware = new \App\Http\Middleware\IdempotencyMiddleware();

        $request = \Illuminate\Http\Request::create('/api/v1/payments', 'POST', [
            'amount' => 1000,
        ], [], [], [
            'HTTP_X_IDEMPOTENCY_KEY' => $idempotencyKey,
        ]);
        $request->setUserResolver(fn() => $this->tenant);

        // First execution
        $response1 = $middleware->handle($request, function ($req) {
            return response()->json(['success' => true, 'order_id' => 999]);
        });

        $this->assertEquals(200, $response1->getStatusCode());
        $this->assertStringContainsString('999', $response1->getContent());

        // Duplicate submission with same idempotency key
        $response2 = $middleware->handle($request, function ($req) {
            $this->fail('Duplicate idempotent request should have returned cached response!');
        });

        $this->assertEquals(200, $response2->getStatusCode());
        $this->assertEquals('HIT-IDEMPOTENT', $response2->headers->get('X-Cache-Lookup'));
    }
}
