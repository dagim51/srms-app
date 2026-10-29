<?php

namespace Tests\Feature;

use App\Domains\Listing\Models\Listing;
use App\Domains\Property\Models\Property;
use App\Domains\Rental\Models\Lease;
use App\Domains\Rental\Models\TenantProfile;
use App\Models\User;
use Database\Seeders\AuthorizationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolePermissionIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $buyer;

    protected User $tenant;

    protected User $host;

    protected User $landlord;

    protected User $agent;

    protected User $developer;

    protected User $company;

    protected User $moderator;

    protected User $financeOfficer;

    protected User $verificationOfficer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AuthorizationSeeder::class);

        $this->admin = User::factory()->create(['status' => 'active', 'email' => 'admin@test.srms']);
        $this->admin->assignRole('Administrator');

        $this->buyer = User::factory()->create(['status' => 'active', 'email' => 'buyer@test.srms']);
        $this->buyer->assignRole('Buyer');

        $this->tenant = User::factory()->create(['status' => 'active', 'email' => 'tenant@test.srms']);
        $this->tenant->assignRole('Tenant');

        $this->host = User::factory()->create(['status' => 'active', 'email' => 'host@test.srms']);
        $this->host->assignRole('Host');

        $this->landlord = User::factory()->create(['status' => 'active', 'email' => 'landlord@test.srms']);
        $this->landlord->assignRole('Landlord');

        $this->agent = User::factory()->create(['status' => 'active', 'email' => 'agent@test.srms']);
        $this->agent->assignRole('Agent');

        $this->developer = User::factory()->create(['status' => 'active', 'email' => 'developer@test.srms']);
        $this->developer->assignRole('Developer');

        $this->company = User::factory()->create(['status' => 'active', 'email' => 'company@test.srms']);
        $this->company->assignRole('Real Estate Company');

        $this->moderator = User::factory()->create(['status' => 'active', 'email' => 'mod@test.srms']);
        $this->moderator->assignRole('Moderator');

        $this->financeOfficer = User::factory()->create(['status' => 'active', 'email' => 'finance@test.srms']);
        $this->financeOfficer->assignRole('Finance Officer');

        $this->verificationOfficer = User::factory()->create(['status' => 'active', 'email' => 'verify@test.srms']);
        $this->verificationOfficer->assignRole('Verification Officer');
    }

    /**
     * Test: Buyer role is strictly limited to marketplace consumer capabilities.
     * Cannot access admin, staff consoles, or create properties/listings.
     */
    public function test_buyer_role_strictly_isolated_from_privileged_features(): void
    {
        // 1. Allowed Consumer Hubs
        $this->actingAs($this->buyer)->get('/dashboard')->assertStatus(200);
        $this->actingAs($this->buyer)->get('/dashboard/favorites')->assertStatus(200);
        $this->actingAs($this->buyer)->get('/dashboard/viewings')->assertStatus(200);
        $this->actingAs($this->buyer)->get('/dashboard/offers')->assertStatus(200);
        $this->actingAs($this->buyer)->get('/dashboard/messages')->assertStatus(200);

        // 2. Denied Management Consoles (403 Forbidden)
        $this->actingAs($this->buyer)->get('/dashboard/admin')->assertStatus(403);
        $this->actingAs($this->buyer)->get('/dashboard/admin/users')->assertStatus(403);
        $this->actingAs($this->buyer)->get('/dashboard/admin/roles')->assertStatus(403);
        $this->actingAs($this->buyer)->get('/dashboard/admin/permissions')->assertStatus(403);
        $this->actingAs($this->buyer)->get('/dashboard/crm')->assertStatus(403);
        $this->actingAs($this->buyer)->get('/dashboard/crm/leads')->assertStatus(403);
        $this->actingAs($this->buyer)->get('/dashboard/projects')->assertStatus(403);
        $this->actingAs($this->buyer)->get('/dashboard/sales/units')->assertStatus(403);
        $this->actingAs($this->buyer)->get('/dashboard/rental')->assertStatus(403);
        $this->actingAs($this->buyer)->get('/dashboard/rental/leases')->assertStatus(403);
        $this->actingAs($this->buyer)->get('/dashboard/rental/applications')->assertStatus(403);
        $this->actingAs($this->buyer)->get('/dashboard/rental/tenants')->assertStatus(403);
        $this->actingAs($this->buyer)->get('/dashboard/accommodations')->assertStatus(403);
        $this->actingAs($this->buyer)->get('/dashboard/organization')->assertStatus(403);
        $this->actingAs($this->buyer)->get('/dashboard/moderation/listings')->assertStatus(403);
        $this->actingAs($this->buyer)->get('/dashboard/finance/ledger')->assertStatus(403);
        $this->actingAs($this->buyer)->get('/dashboard/finance/payouts')->assertStatus(403);
        $this->actingAs($this->buyer)->get('/dashboard/amenities')->assertStatus(403);
        $this->actingAs($this->buyer)->get('/dashboard/locations')->assertStatus(403);

        // 3. Denied Property and Listing Creation
        $this->actingAs($this->buyer)->get('/dashboard/properties/create')->assertStatus(403);
        $this->actingAs($this->buyer)->post('/dashboard/properties', ['title' => 'Unauthorized Property'])->assertStatus(403);
        $this->actingAs($this->buyer)->get('/dashboard/listings/create')->assertStatus(403);
        $this->actingAs($this->buyer)->post('/dashboard/listings', ['price' => 1000])->assertStatus(403);
    }

    /**
     * Test: Tenant role can view own lease but cannot access landlord suites or create properties.
     */
    public function test_tenant_role_isolation_and_lease_ownership(): void
    {
        // 1. Create a lease for this tenant
        $property = Property::create([
            'title' => 'Bole Medhanealem Luxury Condo',
            'property_code' => 'BOLE-CND-001',
            'owner_user_id' => $this->landlord->id,
            'status' => 'available',
        ]);

        $tenantProfile = TenantProfile::create([
            'user_id' => $this->tenant->id,
            'monthly_income' => 45000.0,
            'status' => 'active',
        ]);

        $ownLease = Lease::create([
            'tenant_profile_id' => $tenantProfile->id,
            'property_id' => $property->id,
            'landlord_id' => $this->landlord->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addYear()->toDateString(),
            'rent_amount' => 25000.0,
            'deposit_amount' => 50000.0,
            'status' => 'active',
        ]);

        // Create another tenant's lease
        $otherUser = User::factory()->create(['status' => 'active']);
        $otherProfile = TenantProfile::create([
            'user_id' => $otherUser->id,
            'monthly_income' => 60000.0,
            'status' => 'active',
        ]);
        $otherLease = Lease::create([
            'tenant_profile_id' => $otherProfile->id,
            'property_id' => $property->id,
            'landlord_id' => $this->landlord->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addYear()->toDateString(),
            'rent_amount' => 30000.0,
            'deposit_amount' => 60000.0,
            'status' => 'active',
        ]);

        // Allowed: Tenant can view their own lease
        $this->actingAs($this->tenant)->get("/dashboard/rental/leases/{$ownLease->id}")->assertStatus(200);

        // Denied: Tenant cannot view another user's lease
        $this->actingAs($this->tenant)->get("/dashboard/rental/leases/{$otherLease->id}")->assertStatus(403);

        // Denied: Tenant cannot access landlord rental management
        $this->actingAs($this->tenant)->get('/dashboard/rental')->assertStatus(403);
        $this->actingAs($this->tenant)->get('/dashboard/rental/leases')->assertStatus(403);
        $this->actingAs($this->tenant)->get('/dashboard/rental/applications')->assertStatus(403);
        $this->actingAs($this->tenant)->get('/dashboard/rental/tenants')->assertStatus(403);

        // Denied: Tenant cannot create properties
        $this->actingAs($this->tenant)->get('/dashboard/properties/create')->assertStatus(403);
        $this->actingAs($this->tenant)->get('/dashboard/listings/create')->assertStatus(403);
    }

    /**
     * Test: Host role can access accommodation management but denied on CRM, projects, admin.
     */
    public function test_host_role_isolation(): void
    {
        // Allowed: Accommodation hosting suite
        $this->actingAs($this->host)->get('/dashboard/accommodations')->assertStatus(200);
        $this->actingAs($this->host)->get('/dashboard/accommodations/create')->assertStatus(200);
        $this->actingAs($this->host)->get('/dashboard/properties/create')->assertStatus(200);
        $this->actingAs($this->host)->get('/dashboard/listings/create')->assertStatus(200);

        // Denied: Staff/Corporate consoles
        $this->actingAs($this->host)->get('/dashboard/crm')->assertStatus(403);
        $this->actingAs($this->host)->get('/dashboard/projects')->assertStatus(403);
        $this->actingAs($this->host)->get('/dashboard/organization')->assertStatus(403);
        $this->actingAs($this->host)->get('/dashboard/admin')->assertStatus(403);
        $this->actingAs($this->host)->get('/dashboard/moderation/listings')->assertStatus(403);
        $this->actingAs($this->host)->get('/dashboard/finance/ledger')->assertStatus(403);
    }

    /**
     * Test: Landlord role can access rental operations, but denied on CRM, projects, and admin.
     */
    public function test_landlord_role_isolation(): void
    {
        // Allowed: Rental operations
        $this->actingAs($this->landlord)->get('/dashboard/rental')->assertStatus(200);
        $this->actingAs($this->landlord)->get('/dashboard/rental/leases')->assertStatus(200);
        $this->actingAs($this->landlord)->get('/dashboard/rental/applications')->assertStatus(200);
        $this->actingAs($this->landlord)->get('/dashboard/properties/create')->assertStatus(200);
        $this->actingAs($this->landlord)->get('/dashboard/listings/create')->assertStatus(200);

        // Denied: CRM, projects, admin
        $this->actingAs($this->landlord)->get('/dashboard/crm')->assertStatus(403);
        $this->actingAs($this->landlord)->get('/dashboard/projects')->assertStatus(403);
        $this->actingAs($this->landlord)->get('/dashboard/organization')->assertStatus(403);
        $this->actingAs($this->landlord)->get('/dashboard/admin')->assertStatus(403);
        $this->actingAs($this->landlord)->get('/dashboard/finance/ledger')->assertStatus(403);
    }

    /**
     * Test: Real Estate Company role can access organizations and CRM, but denied on admin and projects.
     */
    public function test_company_role_isolation(): void
    {
        // Allowed: Organization management & CRM
        $this->actingAs($this->company)->get('/dashboard/organization')->assertStatus(200);
        $this->actingAs($this->company)->get('/dashboard/organization/branches')->assertStatus(200);
        $this->actingAs($this->company)->get('/dashboard/organization/members')->assertStatus(200);
        $this->actingAs($this->company)->get('/dashboard/crm')->assertStatus(200);

        // Denied: Developer projects & admin
        $this->actingAs($this->company)->get('/dashboard/projects')->assertStatus(403);
        $this->actingAs($this->company)->get('/dashboard/admin')->assertStatus(403);
        $this->actingAs($this->company)->get('/dashboard/moderation/listings')->assertStatus(403);
    }

    /**
     * Test: Horizontal Resource Isolation (Ownership Enforcement).
     * User A cannot modify or delete User B's property or listing.
     */
    public function test_horizontal_resource_isolation_prevents_unauthorized_tampering(): void
    {
        // Landlord creates a property
        $landlordProp = Property::create([
            'title' => 'Landlord Prime Villa',
            'property_code' => 'VIL-2026-999',
            'owner_user_id' => $this->landlord->id,
            'status' => 'available',
        ]);

        $landlordListing = Listing::create([
            'property_id' => $landlordProp->id,
            'purpose' => 'For Rent',
            'price' => 50000.0,
            'currency' => 'ETB',
            'status' => 'published',
            'visibility' => 'public',
            'owner_actor_id' => $this->landlord->id,
        ]);

        // Host attempts to edit/update/delete Landlord's property
        $this->actingAs($this->host)->get("/dashboard/properties/{$landlordProp->id}/edit")->assertStatus(403);
        $this->actingAs($this->host)->put("/dashboard/properties/{$landlordProp->id}", ['title' => 'Hacked'])->assertStatus(403);
        $this->actingAs($this->host)->delete("/dashboard/properties/{$landlordProp->id}")->assertStatus(403);

        // Host attempts to edit/update/toggle/delete Landlord's listing
        $this->actingAs($this->host)->get("/dashboard/listings/{$landlordListing->id}/edit")->assertStatus(403);
        $this->actingAs($this->host)->put("/dashboard/listings/{$landlordListing->id}", ['purpose' => 'For Sale'])->assertStatus(403);
        $this->actingAs($this->host)->patch("/dashboard/listings/{$landlordListing->id}/toggle-status")->assertStatus(403);
        $this->actingAs($this->host)->delete("/dashboard/listings/{$landlordListing->id}")->assertStatus(403);

        // Administrator CAN edit/delete (Super-admin bypass)
        $this->actingAs($this->admin)->get("/dashboard/properties/{$landlordProp->id}/edit")->assertStatus(200);
        $this->actingAs($this->admin)->get("/dashboard/listings/{$landlordListing->id}/edit")->assertStatus(200);
    }
}
