<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\AuthorizationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolePermissionAuditTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $buyer;
    protected User $tenant;
    protected User $moderator;
    protected User $financeOfficer;
    protected User $agent;
    protected User $developer;
    protected User $verificationOfficer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AuthorizationSeeder::class);

        $this->admin = User::factory()->create(['status' => 'active']);
        $this->admin->assignRole('Administrator');

        $this->buyer = User::factory()->create(['status' => 'active']);
        $this->buyer->assignRole('Buyer');

        $this->tenant = User::factory()->create(['status' => 'active']);
        $this->tenant->assignRole('Tenant');

        $this->moderator = User::factory()->create(['status' => 'active']);
        $this->moderator->assignRole('Moderator');

        $this->financeOfficer = User::factory()->create(['status' => 'active']);
        $this->financeOfficer->assignRole('Finance Officer');

        $this->agent = User::factory()->create(['status' => 'active']);
        $this->agent->assignRole('Agent');

        $this->developer = User::factory()->create(['status' => 'active']);
        $this->developer->assignRole('Developer');

        $this->verificationOfficer = User::factory()->create(['status' => 'active']);
        $this->verificationOfficer->assignRole('Verification Officer');
    }

    /**
     * Test 1: Administrator has access to all operational consoles.
     */
    public function test_admin_has_full_operational_access(): void
    {
        $this->actingAs($this->admin)->get('/dashboard/admin')->assertStatus(200);
        $this->actingAs($this->admin)->get('/dashboard/admin/users')->assertStatus(200);
        $this->actingAs($this->admin)->get('/dashboard/admin/roles')->assertStatus(200);
        $this->actingAs($this->admin)->get('/dashboard/moderation/listings')->assertStatus(200);
        $this->actingAs($this->admin)->get('/dashboard/finance')->assertStatus(200);
        $this->actingAs($this->admin)->get('/dashboard/finance/ledger')->assertStatus(200);
        $this->actingAs($this->admin)->get('/dashboard/trust')->assertStatus(200);
        $this->actingAs($this->admin)->get('/dashboard/amenities')->assertStatus(200);
        $this->actingAs($this->admin)->get('/dashboard/locations')->assertStatus(200);
        $this->actingAs($this->admin)->get('/dashboard/crm')->assertStatus(200);
        $this->actingAs($this->admin)->get('/dashboard/projects')->assertStatus(200);
    }

    /**
     * Test 2: Buyer & Tenant have access to client portal and consumer features, but denied on staff/admin tools.
     */
    public function test_buyer_and_tenant_are_denied_access_to_unpermissioned_staff_actions(): void
    {
        // Allowed client pages
        $this->actingAs($this->buyer)->get('/dashboard')->assertStatus(200);
        $this->actingAs($this->buyer)->get('/dashboard/favorites')->assertStatus(200);
        $this->actingAs($this->buyer)->get('/dashboard/messages')->assertStatus(200);
        $this->actingAs($this->buyer)->get('/dashboard/viewings')->assertStatus(200);
        $this->actingAs($this->buyer)->get('/dashboard/offers')->assertStatus(200);

        // Forbidden staff endpoints for Buyer
        $this->actingAs($this->buyer)->get('/dashboard/admin')->assertStatus(403);
        $this->actingAs($this->buyer)->get('/dashboard/admin/roles')->assertStatus(403);
        $this->actingAs($this->buyer)->get('/dashboard/moderation/listings')->assertStatus(403);
        $this->actingAs($this->buyer)->get('/dashboard/finance/ledger')->assertStatus(403);
        $this->actingAs($this->buyer)->get('/dashboard/finance/payouts')->assertStatus(403);
        $this->actingAs($this->buyer)->get('/dashboard/trust')->assertStatus(403);
        $this->actingAs($this->buyer)->get('/dashboard/amenities')->assertStatus(403);
        $this->actingAs($this->buyer)->get('/dashboard/locations')->assertStatus(403);
        $this->actingAs($this->buyer)->get('/dashboard/crm')->assertStatus(403);
        $this->actingAs($this->buyer)->get('/dashboard/projects')->assertStatus(403);

        // Forbidden staff endpoints for Tenant
        $this->actingAs($this->tenant)->get('/dashboard/admin')->assertStatus(403);
        $this->actingAs($this->tenant)->get('/dashboard/moderation/listings')->assertStatus(403);
        $this->actingAs($this->tenant)->get('/dashboard/finance/ledger')->assertStatus(403);
        $this->actingAs($this->tenant)->get('/dashboard/trust')->assertStatus(403);
    }

    /**
     * Test 3: Moderator can access listing moderation but denied on financial and administrative tools.
     */
    public function test_moderator_access_boundaries(): void
    {
        // Allowed: Moderation queue
        $this->actingAs($this->moderator)->get('/dashboard/moderation/listings')->assertStatus(200);

        // Denied: System administration, financial ledger, and developer tools
        $this->actingAs($this->moderator)->get('/dashboard/admin')->assertStatus(403);
        $this->actingAs($this->moderator)->get('/dashboard/admin/users')->assertStatus(403);
        $this->actingAs($this->moderator)->get('/dashboard/finance/ledger')->assertStatus(403);
        $this->actingAs($this->moderator)->get('/dashboard/crm')->assertStatus(403);
        $this->actingAs($this->moderator)->get('/dashboard/projects')->assertStatus(403);
    }

    /**
     * Test 4: Finance Officer can access financial consoles, but denied on moderation and developer projects.
     */
    public function test_finance_officer_access_boundaries(): void
    {
        // Allowed: Financial hub, ledger, payouts, transactions
        $this->actingAs($this->financeOfficer)->get('/dashboard/finance')->assertStatus(200);
        $this->actingAs($this->financeOfficer)->get('/dashboard/finance/transactions')->assertStatus(200);
        $this->actingAs($this->financeOfficer)->get('/dashboard/finance/ledger')->assertStatus(200);
        $this->actingAs($this->financeOfficer)->get('/dashboard/finance/payouts')->assertStatus(200);

        // Denied: System administration, moderation queue, developer tools
        $this->actingAs($this->financeOfficer)->get('/dashboard/admin')->assertStatus(403);
        $this->actingAs($this->financeOfficer)->get('/dashboard/moderation/listings')->assertStatus(403);
        $this->actingAs($this->financeOfficer)->get('/dashboard/crm')->assertStatus(403);
        $this->actingAs($this->financeOfficer)->get('/dashboard/projects')->assertStatus(403);
    }

    /**
     * Test 5: Agent can access CRM & Leads, but denied on ledger, moderation, and admin settings.
     */
    public function test_agent_access_boundaries(): void
    {
        // Allowed: CRM & Leads
        $this->actingAs($this->agent)->get('/dashboard/crm')->assertStatus(200);
        $this->actingAs($this->agent)->get('/dashboard/crm/leads')->assertStatus(200);

        // Denied: System admin, moderation, finance ledger
        $this->actingAs($this->agent)->get('/dashboard/admin')->assertStatus(403);
        $this->actingAs($this->agent)->get('/dashboard/moderation/listings')->assertStatus(403);
        $this->actingAs($this->agent)->get('/dashboard/finance/ledger')->assertStatus(403);
    }

    /**
     * Test 6: Developer can access Developer Projects & Sales, but denied on admin and finance ledger.
     */
    public function test_developer_access_boundaries(): void
    {
        // Allowed: Projects & Sales
        $this->actingAs($this->developer)->get('/dashboard/projects')->assertStatus(200);
        $this->actingAs($this->developer)->get('/dashboard/sales/units')->assertStatus(200);

        // Denied: System admin, moderation queue, finance ledger
        $this->actingAs($this->developer)->get('/dashboard/admin')->assertStatus(403);
        $this->actingAs($this->developer)->get('/dashboard/moderation/listings')->assertStatus(403);
        $this->actingAs($this->developer)->get('/dashboard/finance/ledger')->assertStatus(403);
    }

    /**
     * Test 7: Verification Officer can access verifications, but denied on financial ledger and admin roles.
     */
    public function test_verification_officer_access_boundaries(): void
    {
        // Allowed: Verification queue
        $this->actingAs($this->verificationOfficer)->get('/dashboard/documents/verifications')->assertStatus(200);

        // Denied: System admin, finance ledger, crm
        $this->actingAs($this->verificationOfficer)->get('/dashboard/admin')->assertStatus(403);
        $this->actingAs($this->verificationOfficer)->get('/dashboard/finance/ledger')->assertStatus(403);
        $this->actingAs($this->verificationOfficer)->get('/dashboard/crm')->assertStatus(403);
    }
}
