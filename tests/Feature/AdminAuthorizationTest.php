<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Property;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Create all roles
        $roles = ['Administrator', 'Property Manager', 'Agent', 'Developer', 'Host', 'Seller', 'User'];
        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }

        // Create admin user
        $this->admin = User::factory()->create([
            'email' => 'admin@srms.et',
        ]);
        $this->admin->assignRole('Administrator');

        // Create regular user
        $this->regularUser = User::factory()->create([
            'email' => 'user@srms.et',
        ]);
        $this->regularUser->assignRole('User');
    }

    /**
     * Test admin users page - role assignment functionality
     */
    public function test_admin_users_page_shows_role_dropdown(): void
    {
        $this->actingAs($this->admin)
            ->get('/dashboard/admin/users/create')
            ->assertStatus(200)
            ->assertSee('role')
            ->assertSee('Administrator')
            ->assertSee('Property Manager')
            ->assertSee('Agent');
    }

    /**
     * Test admin permissions page loads correctly
     */
    public function test_admin_permissions_page_loads(): void
    {
        $this->actingAs($this->admin)
            ->get('/dashboard/admin/permissions')
            ->assertStatus(200)
            ->assertSee('Property & Inventory Management')
            ->assertSee('Marketplace Listings & Moderation')
            ->assertSee('Escrow, Ledger & Financial Payouts')
            ->assertSee('Trust, KYC & Document Verification')
            ->assertSee('System & User Administration');
    }

    /**
     * Test admin audit page loads
     */
    public function test_admin_audit_page_loads(): void
    {
        $this->actingAs($this->admin)
            ->get('/dashboard/admin/audit')
            ->assertStatus(200)
            ->assertSee('AUD-');
    }

    /**
     * Test admin settings page loads
     */
    public function test_admin_settings_page_loads(): void
    {
        $this->actingAs($this->admin)
            ->get('/dashboard/admin/settings')
            ->assertStatus(200);
    }

    /**
     * Test user create with role assignment
     */
    public function test_admin_can_create_user_with_role(): void
    {
        $role = Role::firstOrCreate(['name' => 'Agent']);

        $this->actingAs($this->admin)
            ->post('/dashboard/admin/users', [
                'name' => 'New Agent',
                'email' => 'newagent@srms.et',
                'password' => 'password123',
                'role' => 'Agent',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $user = User::where('email', 'newagent@srms.et')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('Agent'));
    }

    /**
     * Test admin can create users with empty or default prefix phone without UNIQUE constraint violation
     */
    public function test_admin_can_create_users_with_empty_or_prefix_phone(): void
    {
        // 1. First user with '+251' prefix (left uncompleted)
        $this->actingAs($this->admin)
            ->post('/dashboard/admin/users', [
                'name' => 'User Alpha',
                'email' => 'alpha@srms.et',
                'phone' => '+251',
                'password' => 'password123',
                'role' => 'Agent',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $user1 = User::where('email', 'alpha@srms.et')->first();
        $this->assertNotNull($user1);
        $this->assertNull($user1->phone);

        // 2. Second user also with '+251' prefix should not collide with unique constraint
        $this->actingAs($this->admin)
            ->post('/dashboard/admin/users', [
                'name' => 'User Beta',
                'email' => 'beta@srms.et',
                'phone' => '+251 ',
                'password' => 'password123',
                'role' => 'Agent',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $user2 = User::where('email', 'beta@srms.et')->first();
        $this->assertNotNull($user2);
        $this->assertNull($user2->phone);
    }

    /**
     * Test admin can update user role
     */
    public function test_admin_can_update_user_role(): void
    {
        $targetUser = User::factory()->create([
            'email' => 'target@srms.et',
        ]);
        $targetUser->assignRole('User');

        $this->actingAs($this->admin)
            ->post(route('dashboard.admin.users.role', ['id' => $targetUser->id]), [
                'role' => 'Agent',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $targetUser->refresh();
        $this->assertTrue($targetUser->hasRole('Agent'));
    }

    /**
     * Test admin can update permissions
     */
    public function test_admin_can_update_permissions(): void
    {
        // Ensure permissions exist
        $perm1 = Permission::firstOrCreate(['code' => 'properties.view'], ['description' => 'View Properties']);
        $perm2 = Permission::firstOrCreate(['code' => 'listings.submit'], ['description' => 'Submit Listings']);
        $perm3 = Permission::firstOrCreate(['code' => 'finance.ledger.view'], ['description' => 'View Ledger']);

        $role = Role::firstOrCreate(['name' => 'TestRole']);

        $this->actingAs($this->admin)
            ->post('/dashboard/admin/permissions', [
                'role_id' => $role->id,
                'permissions' => ['properties.view', 'listings.submit'],
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $role->refresh();
        $this->assertTrue($role->hasPermissionTo('properties.view'));
        $this->assertTrue($role->hasPermissionTo('listings.submit'));
        $this->assertFalse($role->hasPermissionTo('finance.ledger.view'));
    }

    /**
     * Test non-admin cannot access admin user list
     */
    public function test_regular_user_cannot_access_admin_user_list(): void
    {
        $this->actingAs($this->regularUser)
            ->get('/dashboard/admin/users')
            ->assertStatus(403);
    }

    /**
     * Test non-admin cannot access admin permissions
     */
    public function test_regular_user_cannot_access_admin_permissions(): void
    {
        $this->actingAs($this->regularUser)
            ->get('/dashboard/admin/permissions')
            ->assertStatus(403);
    }

    /**
     * Test non-admin cannot access admin audit
     */
    public function test_regular_user_cannot_access_admin_audit(): void
    {
        $this->actingAs($this->regularUser)
            ->get('/dashboard/admin/audit')
            ->assertStatus(403);
    }

    /**
     * Test non-admin cannot access admin settings
     */
    public function test_regular_user_cannot_access_admin_settings(): void
    {
        $this->actingAs($this->regularUser)
            ->get('/dashboard/admin/settings')
            ->assertStatus(403);
    }

    /**
     * Test admin routes with property manager role (should fail)
     */
    public function test_property_manager_cannot_access_admin_routes(): void
    {
        $pm = User::factory()->create(['email' => 'pm@srms.et']);
        $pmRole = Role::firstOrCreate(['name' => 'Property Manager']);
        $pm->assignRole('Property Manager');

        $this->actingAs($pm)
            ->get('/dashboard/admin')
            ->assertStatus(403);

        $this->actingAs($pm)
            ->get('/dashboard/admin/users')
            ->assertStatus(403);
    }

    /**
     * Test admin routes with agent role (should fail)
     */
    public function test_agent_cannot_access_admin_routes(): void
    {
        $agent = User::factory()->create(['email' => 'agent@srms.et']);
        $agentRole = Role::firstOrCreate(['name' => 'Agent']);
        $agent->assignRole('Agent');

        $this->actingAs($agent)
            ->get('/dashboard/admin')
            ->assertStatus(403);
    }

    /**
     * Test admin routes with developer role (should fail)
     */
    public function test_developer_cannot_access_admin_routes(): void
    {
        $dev = User::factory()->create(['email' => 'dev@srms.et']);
        $devRole = Role::firstOrCreate(['name' => 'Developer']);
        $dev->assignRole('Developer');

        $this->actingAs($dev)
            ->get('/dashboard/admin')
            ->assertStatus(403);
    }

    /**
     * Test admin can view any user
     */
    public function test_admin_can_view_any_user(): void
    {
        $target = User::factory()->create(['email' => 'target@srms.et']);
        $target->assignRole('User');

        $this->actingAs($this->admin)
            ->get("/dashboard/admin/users/{$target->id}")
            ->assertStatus(200)
            ->assertSee($target->name);
    }

    /**
     * Test regular user cannot view other users in admin panel
     */
    public function test_regular_user_cannot_view_admin_user_show(): void
    {
        $target = User::factory()->create(['email' => 'target@srms.et']);

        $this->actingAs($this->regularUser)
            ->get("/dashboard/admin/users/{$target->id}")
            ->assertStatus(403);
    }

    /**
     * Test admin can view user show page for existing user
     */
    public function test_admin_user_show_page_works_for_existing_user(): void
    {
        $target = User::factory()->create([
            'name' => 'Test User',
            'email' => 'testuser@srms.et',
        ]);
        $target->assignRole('User');

        $this->actingAs($this->admin)
            ->get("/dashboard/admin/users/{$target->id}")
            ->assertStatus(200)
            ->assertSee('Test User');
    }
}
