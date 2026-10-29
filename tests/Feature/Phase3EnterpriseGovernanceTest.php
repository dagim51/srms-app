<?php

namespace Tests\Feature;

use App\Domains\Administration\Models\AuditLog;
use App\Domains\Identity\Models\User;
use App\Domains\Listing\Models\Listing;
use App\Domains\Property\Models\Property;
use App\Domains\Rental\Models\Lease;
use App\Domains\Stay\Models\Accommodation;
use App\Domains\Transaction\Models\Invoice;
use App\Events\Bookings\BookingConfirmed;
use App\Events\Listings\ListingPublished;
use App\Events\Rentals\LeaseActivated;
use App\Models\Organization;
use App\Models\Role;
use App\Models\TenantProfile;
use App\Services\Bookings\BookingManager;
use App\Services\Listings\ListingPublisher;
use App\Services\Payments\CbeBirrGateway;
use App\Services\Payments\TelebirrGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class Phase3EnterpriseGovernanceTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected User $ownerUser;

    protected Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::firstOrCreate(['name' => 'Administrator'], ['description' => 'System Administrator']);
        $this->adminUser = User::factory()->create([
            'email' => 'compliance.admin@srms.et',
            'phone' => '+251911999888',
        ]);
        $this->adminUser->roles()->sync([$adminRole->id]);

        $this->ownerUser = User::factory()->create([
            'email' => 'owner.auditable@srms.et',
            'phone' => '+251911222333',
        ]);

        $this->organization = Organization::create([
            'name' => 'Compliance Apex Real Estate',
            'slug' => 'compliance-apex',
            'status' => 'active',
            'owner_user_id' => $this->ownerUser->id,
        ]);
    }

    public function test_property_lifecycle_writes_audit_trail_with_field_diffs(): void
    {
        $this->actingAs($this->adminUser);

        // 1. Creation Audit
        $property = Property::create([
            'property_code' => 'PRP-AUDIT-01',
            'title' => 'Sunshine Luxury Penthouse',
            'description' => 'Top floor luxury penthouse with skylights',
            'price' => 15000000,
            'currency' => 'ETB',
            'status' => 'draft',
            'owner_user_id' => $this->ownerUser->id,
            'organization_id' => $this->organization->id,
        ]);

        $createAudit = AuditLog::where('auditable_id', $property->id)
            ->where('event', 'created')
            ->first();

        $this->assertNotNull($createAudit, 'Property creation should create an audit_logs record');
        $this->assertEquals($this->adminUser->id, $createAudit->actor_id);
        $this->assertEquals('Sunshine Luxury Penthouse', $createAudit->new_values['title'] ?? null);

        // 2. Update Audit
        $property->update([
            'title' => 'Sunshine Luxury Penthouse - Verified',
            'price' => 16500000,
            'status' => 'verified',
        ]);

        $updateAudit = AuditLog::where('auditable_id', $property->id)
            ->where('event', 'updated')
            ->latest('created_at')
            ->first();

        $this->assertNotNull($updateAudit, 'Property update should create an audit_logs record');
        $this->assertEquals($this->adminUser->id, $updateAudit->actor_id);
        $this->assertEquals('Sunshine Luxury Penthouse - Verified', $updateAudit->new_values['title'] ?? null);
        $this->assertEquals('Sunshine Luxury Penthouse', $updateAudit->old_values['title'] ?? null);
        $this->assertEquals(16500000, $updateAudit->new_values['price'] ?? null);

        // 3. Deletion Audit
        $propertyId = $property->id;
        $property->delete();

        $deleteAudit = AuditLog::where('auditable_id', $propertyId)
            ->where('event', 'deleted')
            ->first();

        $this->assertNotNull($deleteAudit, 'Property deletion should create an audit_logs record');
    }

    public function test_user_and_invoice_lifecycle_logs_audit_trail_and_strips_secrets(): void
    {
        $this->actingAs($this->adminUser);

        // 1. User creation with sensitive attributes
        $newUser = User::create([
            'name' => 'Kassahun Worku',
            'email' => 'kassahun.worku@srms.et',
            'password' => bcrypt('SuperSecretPassword123!'),
            'phone' => '+251911445566',
            'locale' => 'am',
            'status' => 'active',
        ]);

        $userAudit = AuditLog::where('auditable_id', $newUser->id)
            ->where('event', 'created')
            ->first();

        $this->assertNotNull($userAudit, 'User creation must generate audit log');
        $this->assertArrayNotHasKey('password', $userAudit->new_values, 'Passwords must NEVER leak into audit logs');
        $this->assertArrayNotHasKey('remember_token', $userAudit->new_values, 'Tokens must NEVER leak into audit logs');
        $this->assertEquals('Kassahun Worku', $userAudit->new_values['name'] ?? null);

        // 2. Invoice lifecycle audit
        $invoice = Invoice::create([
            'user_id' => $newUser->id,
            'amount' => 45000.00,
            'due_date' => now()->addDays(30)->toDateString(),
            'status' => 'pending',
            'notes' => 'Commercial Lease Deposit Q1',
        ]);

        $invoiceAudit = AuditLog::where('auditable_id', $invoice->id)
            ->where('event', 'created')
            ->first();

        $this->assertNotNull($invoiceAudit, 'Invoice creation must generate audit log');
        $this->assertEquals(45000.00, (float) ($invoiceAudit->new_values['amount'] ?? 0));

        // Invoice status update
        $invoice->update(['status' => 'paid']);

        $invoiceUpdateAudit = AuditLog::where('auditable_id', $invoice->id)
            ->where('event', 'updated')
            ->first();

        $this->assertNotNull($invoiceUpdateAudit);
        $this->assertEquals('paid', $invoiceUpdateAudit->new_values['status'] ?? null);
        $this->assertEquals('pending', $invoiceUpdateAudit->old_values['status'] ?? null);
    }

    public function test_domain_events_fire_and_listeners_execute(): void
    {
        Event::fake([
            LeaseActivated::class,
            BookingConfirmed::class,
            ListingPublished::class,
        ]);

        // 1. Lease Activation Domain Event
        $property = Property::create([
            'property_code' => 'PRP-EVENT-01',
            'title' => 'Bole Medhanealem Office Suite',
            'price' => 75000,
            'currency' => 'ETB',
            'status' => 'active',
            'owner_user_id' => $this->ownerUser->id,
            'organization_id' => $this->organization->id,
        ]);

        $tenantProfile = TenantProfile::create([
            'user_id' => $this->ownerUser->id,
            'monthly_income' => 200000,
            'credit_score' => 750,
            'status' => 'verified',
        ]);

        $lease = Lease::create([
            'property_id' => $property->id,
            'tenant_profile_id' => $tenantProfile->id,
            'start_date' => '2026-11-01',
            'end_date' => '2027-10-31',
            'rent_amount' => 75000,
            'deposit_amount' => 150000,
            'status' => 'draft',
        ]);

        $lease->activate();

        Event::assertDispatched(LeaseActivated::class, function ($event) use ($lease) {
            return $event->lease->id === $lease->id;
        });

        // 2. Booking Confirmation Domain Event
        $accommodation = Accommodation::create([
            'property_id' => $property->id,
            'host_id' => $this->ownerUser->id,
            'max_guests' => 4,
            'bedrooms' => 2,
            'beds' => 2,
            'bathrooms' => 2.0,
            'is_active' => true,
        ]);

        $bookingManager = app(BookingManager::class);
        $booking = $bookingManager->createHold([
            'accommodation_id' => $accommodation->id,
            'check_in_date' => '2026-11-10',
            'check_out_date' => '2026-11-15',
            'guests' => 2,
        ], $this->adminUser->id);

        $confirmedBooking = $bookingManager->confirm($booking, $this->adminUser->id);

        Event::assertDispatched(BookingConfirmed::class, function ($event) use ($confirmedBooking) {
            return $event->booking->id === $confirmedBooking->id;
        });

        // 3. Listing Published Domain Event
        $listing = Listing::create([
            'property_id' => $property->id,
            'price' => 75000,
            'purpose' => 'rent',
            'status' => 'draft',
            'owner_actor_id' => $this->ownerUser->id,
        ]);

        $publisher = app(ListingPublisher::class);
        $publishedListing = $publisher->publish($listing, $this->adminUser->id);

        Event::assertDispatched(ListingPublished::class, function ($event) use ($publishedListing) {
            return $event->listing->id === $publishedListing->id;
        });
    }

    public function test_telebirr_and_cbe_birr_gateways_validate_signatures(): void
    {
        // 1. Telebirr HMAC-SHA256 signature verification
        config(['payments.telebirr.app_key' => 'telebirr_secret_key_srms_test']);
        $telebirr = app(TelebirrGateway::class);

        $payload = json_encode([
            'out_trade_no' => 'SRMS-TB-998877',
            'total_amount' => 50000.0,
            'trade_status' => 'SUCCESS',
        ]);

        $validSignature = hash_hmac('sha256', $payload, 'telebirr_secret_key_srms_test');
        $invalidSignature = 'invalid-tampered-signature';

        $this->assertTrue($telebirr->verifyWebhookSignature($payload, $validSignature));
        $this->assertFalse($telebirr->verifyWebhookSignature($payload, $invalidSignature));

        // 2. CBE Birr HMAC-SHA256 signature verification
        $cbeGateway = app(CbeBirrGateway::class);

        $cbePayload = json_encode([
            'out_trade_no' => 'SRMS-CBE-112233',
            'amount' => 75000.0,
            'status' => 'COMPLETED',
        ]);

        $cbeSecret = 'cbe_birr_test_secret_key_12345';
        $validCbeSignature = hash_hmac('sha256', $cbePayload, $cbeSecret);
        $invalidCbeSignature = 'corrupted_signature_123';

        $this->assertTrue($cbeGateway->verifyWebhookSignature($cbePayload, $validCbeSignature));
        $this->assertFalse($cbeGateway->verifyWebhookSignature($cbePayload, $invalidCbeSignature));
    }

    public function test_api_resources_transform_json_responses_without_data_leakage(): void
    {
        $property = Property::create([
            'property_code' => 'PRP-API-TEST-01',
            'title' => 'API Standardized Apartment',
            'description' => 'Full compliance with API specification',
            'price' => 12000000,
            'currency' => 'ETB',
            'status' => 'verified',
            'owner_user_id' => $this->ownerUser->id,
            'organization_id' => $this->organization->id,
        ]);

        Listing::create([
            'property_id' => $property->id,
            'price' => 12000000,
            'purpose' => 'sale',
            'status' => 'pending_review',
            'owner_actor_id' => $this->ownerUser->id,
        ]);

        // 1. Property API Resource Endpoint
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson("/api/v1/properties/{$property->id}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'property_code',
                'title',
                'status',
            ],
        ]);

        // 2. Moderation API Resource Collection Endpoint
        $moderationResponse = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/v1/moderation/listings');

        $moderationResponse->assertStatus(200);
        $moderationResponse->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'purpose',
                    'price',
                    'status',
                ],
            ],
        ]);
    }
}
