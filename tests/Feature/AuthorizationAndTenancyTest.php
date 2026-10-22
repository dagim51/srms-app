<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckPermission;
use App\Http\Middleware\OrganizationScope;
use App\Models\ApiToken;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Tests\TestCase;

class AuthorizationAndTenancyTest extends TestCase
{
    use RefreshDatabase;

    public function test_roles_and_permissions_can_be_assigned()
    {
        $user = User::factory()->create();
        $role = Role::create(['name' => 'Admin']);
        $permission = Permission::create(['code' => 'edit_properties']);

        $role->permissions()->attach($permission->id);
        $user->assignRole($role);

        $this->assertTrue($user->hasRole('Admin'));
        $this->assertTrue($user->hasPermissionTo('edit_properties'));
    }

    public function test_organization_scoped_permissions()
    {
        $user = User::factory()->create();

        $org1 = Str::uuid()->toString();
        $org2 = Str::uuid()->toString();
        DB::table('organizations')->insert([
            ['id' => $org1, 'name' => 'Org 1', 'slug' => 'org-1', 'created_at' => now(), 'updated_at' => now()],
            ['id' => $org2, 'name' => 'Org 2', 'slug' => 'org-2', 'created_at' => now(), 'updated_at' => now()],
        ]);

        $role = Role::create(['name' => 'Manager']);
        $permission = Permission::create(['code' => 'delete_properties']);

        $role->permissions()->attach($permission->id);

        // Assign role only in org1
        $user->assignRole($role, $org1);

        $this->assertTrue($user->hasRole('Manager', $org1));
        $this->assertFalse($user->hasRole('Manager', $org2));

        $this->assertTrue($user->hasPermissionTo('delete_properties', $org1));
        $this->assertFalse($user->hasPermissionTo('delete_properties', $org2));
    }

    public function test_organization_scope_middleware()
    {
        $user = User::factory()->create();
        $orgId = Str::uuid()->toString();

        Route::middleware(['api.token', OrganizationScope::class])->get('/_test_org', function () {
            return response()->json(['success' => true]);
        });

        // Test without belonging to org
        $token = Str::random(40);
        ApiToken::create(['user_id' => $user->id, 'name' => 'test', 'token' => hash('sha256', $token)]);

        $response = $this->withToken($token)
            ->withHeader('X-Organization-ID', $orgId)
            ->getJson('/_test_org');

        $response->assertStatus(403);
        $response->assertJsonPath('error.code', 'FORBIDDEN');

        // Add user to org
        DB::table('organizations')->insert([
            'id' => $orgId, 'name' => 'Test Org', 'slug' => 'test-org', 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('organization_members')->insert([
            'id' => Str::uuid()->toString(), 'user_id' => $user->id, 'organization_id' => $orgId, 'created_at' => now(), 'updated_at' => now(),
        ]);

        $response = $this->withToken($token)
            ->withHeader('X-Organization-ID', $orgId)
            ->getJson('/_test_org');

        $response->assertStatus(200);
    }

    public function test_organization_scope_is_enforced_on_real_api_routes()
    {
        $user = User::factory()->create();
        $orgId = Str::uuid()->toString();

        DB::table('organizations')->insert([
            'id' => $orgId, 'name' => 'Real Org', 'slug' => 'real-org', 'created_at' => now(), 'updated_at' => now(),
        ]);

        $token = Str::random(40);
        ApiToken::create(['user_id' => $user->id, 'name' => 'test', 'token' => hash('sha256', $token)]);

        // Authenticated, but not a member of the organization -> forbidden.
        $this->withToken($token)
            ->withHeader('X-Organization-ID', $orgId)
            ->getJson('/api/v1/users/profile')
            ->assertStatus(403)
            ->assertJsonPath('error.code', 'FORBIDDEN');

        // Join the organization -> allowed.
        DB::table('organization_members')->insert([
            'id' => Str::uuid()->toString(),
            'user_id' => $user->id,
            'organization_id' => $orgId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->withToken($token)
            ->withHeader('X-Organization-ID', $orgId)
            ->getJson('/api/v1/users/profile')
            ->assertStatus(200);

        // No organization header -> unchanged behaviour.
        $this->withToken($token)->getJson('/api/v1/users/profile')->assertStatus(200);
    }

    public function test_check_permission_middleware()
    {
        $user = User::factory()->create();
        $role = Role::create(['name' => 'Editor']);
        $permission = Permission::create(['code' => 'edit_post']);
        $role->permissions()->attach($permission->id);
        $user->assignRole($role);

        Route::middleware(['api.token', CheckPermission::class.':edit_post'])->get('/_test_perm', function () {
            return response()->json(['success' => true]);
        });

        $token = Str::random(40);
        ApiToken::create(['user_id' => $user->id, 'name' => 'test', 'token' => hash('sha256', $token)]);

        // User has permission globally
        $response = $this->withToken($token)->getJson('/_test_perm');
        $response->assertStatus(200);

        // Test for permission they don't have
        Route::middleware(['api.token', CheckPermission::class.':delete_post'])->get('/_test_perm2', function () {
            return response()->json(['success' => true]);
        });

        $response2 = $this->withToken($token)->getJson('/_test_perm2');
        $response2->assertStatus(403);
        $response2->assertJsonPath('error.code', 'FORBIDDEN');
    }
}
