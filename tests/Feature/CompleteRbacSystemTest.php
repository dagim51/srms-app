<?php

namespace Tests\Feature;

use App\Domains\Property\Policies\PropertyPolicy;
use App\Domains\Transaction\Models\Document;
use App\Models\ApiToken;
use App\Models\Organization;
use App\Models\Permission;
use App\Models\Property;
use App\Models\PropertyType;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\AuthorizationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Tests\TestCase;

class CompleteRbacSystemTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $buyer;

    protected User $tenant;

    protected User $owner;

    protected User $host;

    protected User $verificationOfficer;

    protected Organization $org1;

    protected Organization $org2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(AuthorizationSeeder::class);

        $this->org1 = Organization::create([
            'id' => (string) Str::uuid(),
            'name' => 'Sheger Real Estate Group',
            'slug' => 'sheger-real-estate',
        ]);

        $this->org2 = Organization::create([
            'id' => (string) Str::uuid(),
            'name' => 'Entoto Luxury Developments',
            'slug' => 'entoto-luxury',
        ]);

        $this->admin = User::factory()->create([
            'name' => 'Platform Super Admin',
            'email' => 'superadmin@srms.et',
            'status' => 'active',
        ]);
        $this->admin->assignRole('Administrator');

        $this->buyer = User::factory()->create([
            'name' => 'Almaz Tefera',
            'email' => 'almaz.buyer@srms.et',
            'status' => 'active',
        ]);
        $this->buyer->assignRole('Buyer');

        $this->tenant = User::factory()->create([
            'name' => 'Yonas Bekele',
            'email' => 'yonas.tenant@srms.et',
            'status' => 'active',
        ]);
        $this->tenant->assignRole('Tenant');

        $this->owner = User::factory()->create([
            'name' => 'Kassahun Desta',
            'email' => 'kassahun.owner@srms.et',
            'status' => 'active',
        ]);
        $this->owner->assignRole('Property Owner');

        $this->host = User::factory()->create([
            'name' => 'Selamawit Haile',
            'email' => 'selam.host@srms.et',
            'status' => 'active',
        ]);
        $this->host->assignRole('Host');

        $this->verificationOfficer = User::factory()->create([
            'name' => 'Solomon Compliance',
            'email' => 'solomon.kyc@srms.et',
            'status' => 'active',
        ]);
        $this->verificationOfficer->assignRole('Verification Officer');
    }

    /**
     * Requirement 1: An unauthenticated visitor can access permitted public pages.
     */
    public function test_01_unauthenticated_visitor_can_access_permitted_public_pages(): void
    {
        $responseMarketplace = $this->get('/properties');
        $responseMarketplace->assertStatus(200);

        $responseSearch = $this->get('/search');
        $responseSearch->assertStatus(200);
    }

    /**
     * Requirement 2: An unauthenticated visitor cannot access protected actions.
     */
    public function test_02_unauthenticated_visitor_cannot_access_protected_actions(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/dashboard/properties/create')->assertRedirect('/login');
        $this->get('/dashboard/admin/roles')->assertRedirect('/login');
        $this->get('/dashboard/admin/permissions')->assertRedirect('/login');
    }

    /**
     * Requirement 3: An authorized user can perform an action allowed by their permission.
     */
    public function test_03_authorized_user_can_perform_action_allowed_by_permission(): void
    {
        $this->assertTrue($this->owner->hasPermissionTo('properties.create'));
        $this->assertTrue($this->owner->hasPermissionTo('properties.edit'));

        $response = $this->actingAs($this->owner)->get(route('dashboard.properties.index'));
        $response->assertStatus(200);
    }

    /**
     * Requirement 4: A user without the required permission is denied.
     */
    public function test_04_user_without_required_permission_is_denied(): void
    {
        // Tenant does not have Administrator role or system administration permissions
        $response = $this->actingAs($this->tenant)->get('/dashboard/admin/roles');
        $response->assertStatus(403);

        $responsePerms = $this->actingAs($this->buyer)->get('/dashboard/admin/permissions');
        $responsePerms->assertStatus(403);
    }

    /**
     * Requirement 5: A user with multiple roles receives the intended combined permissions.
     */
    public function test_05_user_with_multiple_roles_receives_combined_permissions(): void
    {
        $multiUser = User::factory()->create(['status' => 'active']);
        $multiUser->assignRole('Property Owner');
        $multiUser->assignRole('Host');

        $this->assertTrue($multiUser->hasRole('Property Owner'));
        $this->assertTrue($multiUser->hasRole('Host'));

        // Property Owner permissions
        $this->assertTrue($multiUser->hasPermissionTo('properties.create'));
        $this->assertTrue($multiUser->hasPermissionTo('properties.edit'));

        // Host permissions
        $this->assertTrue($multiUser->hasPermissionTo('accommodations.manage'));
        $this->assertTrue($multiUser->hasPermissionTo('bookings.manage'));
        $this->assertTrue($multiUser->hasPermissionTo('housekeeping.manage'));

        // Does NOT receive administrative or developer permissions
        $this->assertFalse($multiUser->hasPermissionTo('roles.manage'));
        $this->assertFalse($multiUser->hasPermissionTo('developments.manage'));
    }

    /**
     * Requirement 6: Revoking a role or permission takes effect correctly.
     */
    public function test_06_revoking_role_or_permission_takes_effect_correctly(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->assignRole('Property Owner');
        $user->assignRole('Host');

        $this->assertTrue($user->hasPermissionTo('accommodations.manage'));

        // Revoke Host role
        $user->removeRole('Host');

        $this->assertFalse($user->hasRole('Host'));
        $this->assertFalse($user->hasPermissionTo('accommodations.manage'));
        $this->assertFalse($user->hasPermissionTo('bookings.manage'));

        // Still retains Property Owner capability
        $this->assertTrue($user->hasPermissionTo('properties.create'));
    }

    /**
     * Requirement 7: A user cannot access another user's private records by changing a record ID.
     */
    public function test_07_user_cannot_access_another_users_private_records_by_changing_record_id(): void
    {
        $propType = PropertyType::firstOrCreate(['code' => 'TEST-VILLA'], [
            'id' => (string) Str::uuid(),
            'name_en' => 'Villa',
        ]);

        $ownerProperty = Property::create([
            'property_code' => 'PROP-OWNER-01',
            'owner_user_id' => $this->owner->id,
            'type_id' => $propType->id,
            'title' => 'Kassahun Private Villa',
            'status' => 'active',
        ]);

        // Malicious or accidental URL tampering by another user (tenant or buyer)
        $tamperResponse = $this->actingAs($this->tenant)->get(route('dashboard.properties.edit', $ownerProperty->id));
        $tamperResponse->assertStatus(403);

        $updateResponse = $this->actingAs($this->tenant)->put(route('dashboard.properties.update', $ownerProperty->id), [
            'title' => 'Hacked Villa Title',
        ]);
        $updateResponse->assertStatus(403);

        // Verification that database was not modified
        $ownerProperty->refresh();
        $this->assertSame('Kassahun Private Villa', $ownerProperty->title);
    }

    /**
     * Requirement 8: Company and branch boundaries are enforced where applicable.
     */
    public function test_08_company_and_branch_boundaries_are_enforced_where_applicable(): void
    {
        $agent = User::factory()->create(['status' => 'active']);
        $agent->assignRole('Agent', $this->org1->id);

        // Agent is authorized in Sheger Real Estate Group
        $this->assertTrue($agent->hasRole('Agent', $this->org1->id));
        $this->assertTrue($agent->hasPermissionTo('crm.leads.manage', $this->org1->id));

        // Agent is strictly forbidden in Entoto Luxury Developments
        $this->assertFalse($agent->hasRole('Agent', $this->org2->id));
        $this->assertFalse($agent->hasPermissionTo('crm.leads.manage', $this->org2->id));

        // Agent is not a global un-scoped platform agent
        $this->assertFalse($agent->hasExactRole('Agent', null));
    }

    /**
     * Requirement 9: Property ownership and assignment restrictions are enforced.
     */
    public function test_09_property_ownership_and_assignment_restrictions_are_enforced(): void
    {
        $propType = PropertyType::firstOrCreate(['code' => 'TEST-APT'], [
            'id' => (string) Str::uuid(),
            'name_en' => 'Apartment',
        ]);

        $property = Property::create([
            'property_code' => 'PROP-AUTH-01',
            'owner_user_id' => $this->owner->id,
            'type_id' => $propType->id,
            'title' => 'Bole Sunlit Apartment',
            'status' => 'active',
        ]);

        $policy = new PropertyPolicy;

        // 1. Owner can update
        $this->assertTrue($policy->update($this->owner, $property));

        // 2. Global Administrator can update
        $this->assertTrue($policy->update($this->admin, $property));

        // 3. Unrelated user (tenant or buyer) cannot update
        $this->assertFalse($policy->update($this->tenant, $property));
        $this->assertFalse($policy->update($this->buyer, $property));
    }

    /**
     * Requirement 10: Sensitive documents remain inaccessible without the correct authorization.
     */
    public function test_10_sensitive_documents_remain_inaccessible_without_the_correct_authorization(): void
    {
        // 1. Private Document
        $privateDoc = Document::create([
            'user_id' => $this->owner->id,
            'name' => 'Owner Private Financial Statement.pdf',
            'type' => 'Financial Document',
            'status' => 'verified',
            'access_level' => 'private',
            'file_path' => 'documents/financial_owner.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 1024000,
        ]);

        $this->assertTrue(Gate::forUser($this->owner)->allows('view', $privateDoc));
        $this->assertTrue(Gate::forUser($this->admin)->allows('view', $privateDoc));
        $this->assertFalse(Gate::forUser($this->tenant)->allows('view', $privateDoc));

        // 2. Verification-only Document
        $kycDoc = Document::create([
            'user_id' => $this->tenant->id,
            'name' => 'Tenant Kebele ID.pdf',
            'type' => 'Identity Document',
            'status' => 'pending',
            'access_level' => 'verification-only',
            'file_path' => 'documents/kebele_tenant.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 512000,
        ]);

        $this->assertTrue(Gate::forUser($this->tenant)->allows('view', $kycDoc)); // Owner
        $this->assertTrue(Gate::forUser($this->verificationOfficer)->allows('view', $kycDoc)); // Officer
        $this->assertTrue(Gate::forUser($this->admin)->allows('view', $kycDoc)); // Superadmin
        $this->assertFalse(Gate::forUser($this->owner)->allows('view', $kycDoc)); // Other user denied

        // 3. Admin-only Document
        $adminDoc = Document::create([
            'user_id' => $this->admin->id,
            'name' => 'Platform AML Compliance Audit.pdf',
            'type' => 'System Document',
            'status' => 'verified',
            'access_level' => 'admin-only',
            'file_path' => 'documents/compliance_audit.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 2048000,
        ]);

        $this->assertTrue(Gate::forUser($this->admin)->allows('view', $adminDoc));
        $this->assertFalse(Gate::forUser($this->verificationOfficer)->allows('view', $adminDoc));
        $this->assertFalse(Gate::forUser($this->buyer)->allows('view', $adminDoc));

        // 4. Public Document
        $publicDoc = Document::create([
            'user_id' => $this->owner->id,
            'name' => 'Public Villa Brochure.pdf',
            'type' => 'Marketing',
            'status' => 'verified',
            'access_level' => 'public',
            'file_path' => 'documents/brochure.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 300000,
        ]);

        $this->assertTrue(Gate::forUser($this->buyer)->allows('view', $publicDoc));
        $this->assertTrue(Gate::forUser($this->tenant)->allows('view', $publicDoc));
    }

    /**
     * Requirement 11: Only authorized users can create or modify roles and permissions.
     */
    public function test_11_only_authorized_users_can_create_or_modify_roles_and_permissions(): void
    {
        // 1. Admin can access roles management page
        $this->actingAs($this->admin)->get(route('dashboard.admin.roles'))->assertStatus(200);

        // 2. Admin can create custom role
        $createResponse = $this->actingAs($this->admin)->post(route('dashboard.admin.roles.store'), [
            'name' => 'Custom Escrow Auditor',
            'description' => 'Third party certified audit specialist',
        ]);
        $createResponse->assertRedirect(route('dashboard.admin.roles'));

        $this->assertDatabaseHas('roles', [
            'name' => 'Custom Escrow Auditor',
            'is_system' => false,
        ]);

        // 3. Non-admin user receives 403 Forbidden
        $unauthorizedResponse = $this->actingAs($this->tenant)->post(route('dashboard.admin.roles.store'), [
            'name' => 'Hacker Elevation Role',
            'description' => 'Attempted privilege escalation',
        ]);
        $unauthorizedResponse->assertStatus(403);
        $this->assertDatabaseMissing('roles', ['name' => 'Hacker Elevation Role']);
    }

    /**
     * Requirement 12: Unauthorized users cannot assign roles or elevate their own privileges.
     */
    public function test_12_unauthorized_users_cannot_assign_roles_or_elevate_privileges(): void
    {
        $targetUser = User::factory()->create(['status' => 'active']);
        $targetUser->assignRole('Buyer');

        // Tenant tries to elevate target user to Administrator
        $response = $this->actingAs($this->tenant)->post("/dashboard/admin/users/{$targetUser->id}/role", [
            'roles' => ['Administrator'],
        ]);
        $response->assertStatus(403);

        $targetUser->refresh();
        $this->assertFalse($targetUser->hasRole('Administrator'));
        $this->assertTrue($targetUser->hasRole('Buyer'));
    }

    /**
     * Requirement 13: Critical administrative access cannot be accidentally removed in violation of the defined policy.
     */
    public function test_13_critical_administrative_access_cannot_be_accidentally_removed(): void
    {
        // Ensure $this->admin is the only active Administrator on the platform
        User::where('id', '!=', $this->admin->id)->each(function ($u) {
            $u->roles()->detach();
        });

        // Admin tries to remove Administrator role from their own account (or another admin tries)
        $response = $this->actingAs($this->admin)->post("/dashboard/admin/users/{$this->admin->id}/role", [
            'roles' => ['Buyer'], // Omitted 'Administrator'
        ]);

        $response->assertSessionHas('error');
        $this->admin->refresh();
        // Administrator role must remain intact to prevent lockout!
        $this->assertTrue($this->admin->hasRole('Administrator'));
    }

    /**
     * Requirement 14: Invalid or duplicate role/permission assignments are handled safely.
     */
    public function test_14_invalid_or_duplicate_role_permission_assignments_handled_safely(): void
    {
        $user = User::factory()->create(['status' => 'active']);

        // Assign same role twice
        $user->assignRole('Buyer');
        $user->assignRole('Buyer');

        $assignedCount = DB::table('user_roles')
            ->where('user_id', $user->id)
            ->where('role_id', Role::where('name', 'Buyer')->first()->id)
            ->count();

        $this->assertSame(1, $assignedCount, 'Duplicate assignment should be idempotent.');

        // Sync with duplicate array values
        $user->syncRoles(['Buyer', 'Buyer', 'Tenant']);
        $rolesCount = $user->roles()->count();
        $this->assertSame(2, $rolesCount);
        $this->assertTrue($user->hasRole('Buyer'));
        $this->assertTrue($user->hasRole('Tenant'));
    }

    /**
     * Requirement 15: Existing authentication and business workflows continue to work.
     */
    public function test_15_existing_authentication_and_business_workflows_continue_to_work(): void
    {
        $response = $this->actingAs($this->buyer)->get('/dashboard');
        $response->assertStatus(200);

        $responseFavorites = $this->actingAs($this->buyer)->get('/dashboard/favorites');
        $responseFavorites->assertStatus(200);
    }

    /**
     * Requirement 16: Web and API authorization responses use appropriate behavior.
     */
    public function test_16_web_and_api_authorization_responses_use_appropriate_behavior(): void
    {
        // Setup API route with role middleware
        Route::middleware(['api.token', 'role:Administrator'])->get('/_test_api_rbac_check', function () {
            return response()->json(['success' => true]);
        });

        // 1. Unauthenticated API returns 401
        $this->getJson('/_test_api_rbac_check')
            ->assertStatus(401)
            ->assertJsonPath('error.code', 'UNAUTHENTICATED');

        // 2. Unauthorized API user returns 403
        $buyerToken = Str::random(40);
        ApiToken::create(['user_id' => $this->buyer->id, 'name' => 'buyer_token', 'token' => hash('sha256', $buyerToken)]);

        $this->withToken($buyerToken)
            ->getJson('/_test_api_rbac_check')
            ->assertStatus(403)
            ->assertJsonPath('error.code', 'FORBIDDEN');

        // 3. Authorized API user returns 200
        $adminToken = Str::random(40);
        ApiToken::create(['user_id' => $this->admin->id, 'name' => 'admin_token', 'token' => hash('sha256', $adminToken)]);

        $this->withToken($adminToken)
            ->getJson('/_test_api_rbac_check')
            ->assertStatus(200)
            ->assertJsonPath('success', true);
    }

    /**
     * Requirement 17: Database migrations and seeders run successfully and can safely be rerun.
     */
    public function test_17_database_seeders_run_successfully_and_can_safely_be_rerun(): void
    {
        // Re-run AuthorizationSeeder to verify strict idempotency
        $this->seed(AuthorizationSeeder::class);
        $this->seed(AuthorizationSeeder::class);

        $canonicalRoles = [
            'Guest', 'Buyer', 'Tenant', 'Guest / Traveler',
            'Property Owner', 'Host', 'Landlord', 'Agent',
            'Real Estate Company', 'Developer', 'Property Manager',
            'Professional Service Provider', 'Moderator', 'Verification Officer',
            'Finance Officer', 'Customer Support', 'Administrator',
        ];

        foreach ($canonicalRoles as $roleName) {
            $role = Role::where('name', $roleName)->first();
            $this->assertNotNull($role, "Role {$roleName} must exist in the database.");
            $this->assertTrue($role->is_system, "Role {$roleName} must be marked as system role.");
            $this->assertNotEmpty($role->description, "Role {$roleName} must have a description.");
        }
    }

    /**
     * Requirement 18: Permission updates and cached authorization state remain consistent.
     */
    public function test_18_permission_updates_and_cached_authorization_state_remain_consistent(): void
    {
        $customRole = Role::create([
            'name' => 'Special Inspector',
            'guard_name' => 'api',
            'status' => 'active',
            'is_system' => false,
        ]);

        $inspector = User::factory()->create(['status' => 'active']);
        $inspector->assignRole('Special Inspector');

        $this->assertFalse($inspector->hasPermissionTo('listings.moderate'));

        // Assign permission directly to the role
        $perm = Permission::where('code', 'listings.moderate')->firstOrFail();
        $customRole->permissions()->attach($perm->id);

        // Immediate consistency check
        $this->assertTrue($inspector->hasPermissionTo('listings.moderate'));

        // Detach permission
        $customRole->permissions()->detach($perm->id);
        $this->assertFalse($inspector->hasPermissionTo('listings.moderate'));
    }
}
