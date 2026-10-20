<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Organization;
use App\Models\Property;
use App\Models\PropertyType;
use App\Models\Lease;
use App\Models\TenantProfile;
use App\Models\ApiToken;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class RoleBasedAccessControlTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $tenantUser;
    protected Organization $org1;
    protected Organization $org2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\AuthorizationSeeder::class);

        $this->org1 = Organization::create([
            'id' => (string) Str::uuid(),
            'name' => 'Addis Real Estate PLC',
            'slug' => 'addis-real-estate',
        ]);

        $this->org2 = Organization::create([
            'id' => (string) Str::uuid(),
            'name' => 'Bole Horizon Developers',
            'slug' => 'bole-horizon',
        ]);

        $this->admin = User::factory()->create([
            'email' => 'admin@srms.et',
            'status' => 'active',
        ]);
        $this->admin->assignRole('Administrator'); // Global assignment (organization_id IS NULL)

        $this->tenantUser = User::factory()->create([
            'email' => 'tenant@srms.et',
            'status' => 'active',
        ]);
        $this->tenantUser->assignRole('User');
    }

    public function test_global_administrator_has_access_across_all_organization_scopes(): void
    {
        // 1. Verify role resolution
        $this->assertTrue($this->admin->hasRole('Administrator'));
        $this->assertTrue($this->admin->hasRole('Administrator', $this->org1->id));
        $this->assertTrue($this->admin->hasRole('Administrator', $this->org2->id));

        // 2. Verify permission resolution
        $this->assertTrue($this->admin->hasPermissionTo('properties.view'));
        $this->assertTrue($this->admin->hasPermissionTo('properties.view', $this->org1->id));
        $this->assertTrue($this->admin->hasPermissionTo('finance.ledger.view', $this->org2->id));

        // 3. Verify policy check on organization-owned model
        $propType = PropertyType::firstOrCreate(['code' => 'APT'], [
            'id' => (string) Str::uuid(),
            'name_en' => 'Apartment',
        ]);
        $property = Property::create([
            'property_code' => 'PROP-BOLE-001',
            'organization_id' => $this->org1->id,
            'owner_user_id' => $this->tenantUser->id,
            'type_id' => $propType->id,
            'title' => 'Luxury Bole Loft',
            'status' => 'active',
        ]);

        $policy = new \App\Policies\PropertyPolicy();
        $this->assertTrue($policy->update($this->admin, $property));
    }

    public function test_organization_scoped_roles_remain_strictly_isolated(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('Property Manager', $this->org1->id);

        // Authorized in org1
        $this->assertTrue($manager->hasRole('Property Manager', $this->org1->id));
        $this->assertTrue($manager->hasPermissionTo('leases.create', $this->org1->id));

        // Forbidden in org2
        $this->assertFalse($manager->hasRole('Property Manager', $this->org2->id));
        $this->assertFalse($manager->hasPermissionTo('leases.create', $this->org2->id));

        // Forbidden globally (not a global platform role)
        $this->assertFalse($manager->hasExactRole('Property Manager', null));
    }

    public function test_has_any_role_and_has_all_roles_utility_methods(): void
    {
        $agent = User::factory()->create();
        $agent->assignRole('Agent');

        $this->assertTrue($agent->hasAnyRole(['Administrator', 'Agent']));
        $this->assertTrue($agent->hasAnyRole('Administrator|Agent'));
        $this->assertTrue($agent->hasAnyRole('Administrator,Agent'));
        $this->assertFalse($agent->hasAnyRole(['Administrator', 'Developer']));

        $this->assertTrue($agent->hasAllRoles(['Agent']));
        $this->assertFalse($agent->hasAllRoles(['Agent', 'Administrator']));
    }

    public function test_assign_remove_and_sync_roles(): void
    {
        $user = User::factory()->create();

        $user->assignRole('Developer');
        $this->assertTrue($user->hasRole('Developer'));

        $user->removeRole('Developer');
        $this->assertFalse($user->hasRole('Developer'));

        $user->syncRoles(['Host', 'Seller']);
        $this->assertTrue($user->hasRole('Host'));
        $this->assertTrue($user->hasRole('Seller'));
        $this->assertFalse($user->hasRole('Developer'));
    }

    public function test_direct_permissions_and_syncing(): void
    {
        $user = User::factory()->create();

        $user->givePermissionTo('properties.gis');
        $this->assertTrue($user->hasPermissionTo('properties.gis'));

        $user->revokePermissionTo('properties.gis');
        $this->assertFalse($user->hasPermissionTo('properties.gis'));

        $user->syncPermissions(['properties.gis', 'listings.feature']);
        $this->assertTrue($user->hasPermissionTo('properties.gis'));
        $this->assertTrue($user->hasPermissionTo('listings.feature'));
    }

    public function test_check_role_middleware_enforces_access_on_api_and_web(): void
    {
        Route::middleware(['api.token', 'role:Administrator'])->get('/_test_api_role', function () {
            return response()->json(['status' => 'authorized']);
        });

        // 1. Unauthenticated API request
        $this->getJson('/_test_api_role')
            ->assertStatus(401)
            ->assertJsonPath('error.code', 'UNAUTHENTICATED');

        // 2. Non-admin user forbidden
        $token = Str::random(40);
        ApiToken::create(['user_id' => $this->tenantUser->id, 'name' => 'test', 'token' => hash('sha256', $token)]);

        $this->withToken($token)
            ->getJson('/_test_api_role')
            ->assertStatus(403)
            ->assertJsonPath('error.code', 'FORBIDDEN');

        // 3. Admin user authorized
        $adminToken = Str::random(40);
        ApiToken::create(['user_id' => $this->admin->id, 'name' => 'test_admin', 'token' => hash('sha256', $adminToken)]);

        $this->withToken($adminToken)
            ->getJson('/_test_api_role')
            ->assertStatus(200)
            ->assertJsonPath('status', 'authorized');

        // 4. Web route: non-admin gets 403 on /dashboard/admin
        $this->actingAs($this->tenantUser)
            ->get(route('dashboard.admin.index'))
            ->assertStatus(403);

        // 5. Web route: admin gets 200 on /dashboard/admin
        $this->actingAs($this->admin)
            ->get(route('dashboard.admin.index'))
            ->assertStatus(200);
    }

    public function test_check_permission_middleware_supports_pipe_syntax(): void
    {
        Route::middleware(['api.token', 'permission:properties.create|properties.edit'])->get('/_test_api_perm', function () {
            return response()->json(['status' => 'permitted']);
        });

        $user = User::factory()->create();
        $user->assignRole('Seller'); // Seller has properties.create

        $token = Str::random(40);
        ApiToken::create(['user_id' => $user->id, 'name' => 'test_seller', 'token' => hash('sha256', $token)]);

        $this->withToken($token)
            ->getJson('/_test_api_perm')
            ->assertStatus(200)
            ->assertJsonPath('status', 'permitted');

        // User with no relevant permissions gets 403
        $guestToken = Str::random(40);
        ApiToken::create(['user_id' => $this->tenantUser->id, 'name' => 'test_tenant', 'token' => hash('sha256', $guestToken)]);

        $this->withToken($guestToken)
            ->getJson('/_test_api_perm')
            ->assertStatus(403)
            ->assertJsonPath('error.code', 'FORBIDDEN');
    }

    public function test_gate_and_can_directive_integration(): void
    {
        // 1. Super-admin can do any action via Gate::before
        $this->assertTrue(Gate::forUser($this->admin)->allows('any.arbitrary.ability'));

        // 2. Regular user with permission allowed
        $manager = User::factory()->create();
        $manager->assignRole('Property Manager'); // has kyc.verify
        $this->assertTrue(Gate::forUser($manager)->allows('kyc.verify'));

        // 3. Regular user without permission denied
        $this->assertFalse(Gate::forUser($manager)->allows('disputes.arbitrate'));
    }

    public function test_admin_can_update_user_role_from_web(): void
    {
        $targetUser = User::factory()->create();
        $targetUser->assignRole('User');

        $this->actingAs($this->admin)
            ->post(route('dashboard.admin.users.role', $targetUser->id), [
                'role' => 'Property Manager',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertTrue($targetUser->fresh()->hasRole('Property Manager'));
    }
}
