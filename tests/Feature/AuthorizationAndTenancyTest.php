<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthorizationAndTenancyTest extends TestCase
{
    use RefreshDatabase;

    public function test_roles_and_permissions_can_be_assigned()
    {
        $user = \App\Models\User::factory()->create();
        $role = \App\Models\Role::create(['name' => 'Admin']);
        $permission = \App\Models\Permission::create(['code' => 'edit_properties']);
        
        $role->permissions()->attach($permission->id);
        $user->assignRole($role);

        $this->assertTrue($user->hasRole('Admin'));
        $this->assertTrue($user->hasPermissionTo('edit_properties'));
    }

    public function test_organization_scoped_permissions()
    {
        $user = \App\Models\User::factory()->create();
        
        $org1 = \Illuminate\Support\Str::uuid()->toString();
        $org2 = \Illuminate\Support\Str::uuid()->toString();
        \Illuminate\Support\Facades\DB::table('organizations')->insert([
            ['id' => $org1, 'name' => 'Org 1', 'slug' => 'org-1', 'created_at' => now(), 'updated_at' => now()],
            ['id' => $org2, 'name' => 'Org 2', 'slug' => 'org-2', 'created_at' => now(), 'updated_at' => now()]
        ]);

        $role = \App\Models\Role::create(['name' => 'Manager']);
        $permission = \App\Models\Permission::create(['code' => 'delete_properties']);
        
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
        $user = \App\Models\User::factory()->create();
        $orgId = \Illuminate\Support\Str::uuid()->toString();

        \Illuminate\Support\Facades\Route::middleware(['api.token', \App\Http\Middleware\OrganizationScope::class])->get('/_test_org', function() {
            return response()->json(['success' => true]);
        });

        // Test without belonging to org
        $token = \Illuminate\Support\Str::random(40);
        \App\Models\ApiToken::create(['user_id' => $user->id, 'name' => 'test', 'token' => hash('sha256', $token)]);
        
        $response = $this->withToken($token)
            ->withHeader('X-Organization-ID', $orgId)
            ->getJson('/_test_org');
            
        $response->assertStatus(403);
        $response->assertJsonPath('error.code', 'FORBIDDEN');

        // Add user to org
        \Illuminate\Support\Facades\DB::table('organizations')->insert([
            'id' => $orgId, 'name' => 'Test Org', 'slug' => 'test-org', 'created_at' => now(), 'updated_at' => now()
        ]);
        \Illuminate\Support\Facades\DB::table('organization_members')->insert([
            'id' => \Illuminate\Support\Str::uuid()->toString(), 'user_id' => $user->id, 'organization_id' => $orgId, 'created_at' => now(), 'updated_at' => now()
        ]);

        $response = $this->withToken($token)
            ->withHeader('X-Organization-ID', $orgId)
            ->getJson('/_test_org');
            
        $response->assertStatus(200);
    }

    public function test_check_permission_middleware()
    {
        $user = \App\Models\User::factory()->create();
        $role = \App\Models\Role::create(['name' => 'Editor']);
        $permission = \App\Models\Permission::create(['code' => 'edit_post']);
        $role->permissions()->attach($permission->id);
        $user->assignRole($role);

        \Illuminate\Support\Facades\Route::middleware(['api.token', \App\Http\Middleware\CheckPermission::class.':edit_post'])->get('/_test_perm', function() {
            return response()->json(['success' => true]);
        });

        $token = \Illuminate\Support\Str::random(40);
        \App\Models\ApiToken::create(['user_id' => $user->id, 'name' => 'test', 'token' => hash('sha256', $token)]);

        // User has permission globally
        $response = $this->withToken($token)->getJson('/_test_perm');
        $response->assertStatus(200);

        // Test for permission they don't have
        \Illuminate\Support\Facades\Route::middleware(['api.token', \App\Http\Middleware\CheckPermission::class.':delete_post'])->get('/_test_perm2', function() {
            return response()->json(['success' => true]);
        });
        
        $response2 = $this->withToken($token)->getJson('/_test_perm2');
        $response2->assertStatus(403);
        $response2->assertJsonPath('error.code', 'FORBIDDEN');
    }
}
