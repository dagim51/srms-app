<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LocationHierarchyTest extends TestCase
{
    use RefreshDatabase;

    public function test_location_multilingual_and_hierarchy()
    {
        $country = \App\Models\Country::create([
            'code' => 'ET',
            'name_en' => 'Ethiopia',
            'name_am' => 'ኢትዮጵያ',
            'name_om' => 'Itoophiyaa',
            'status' => 'active'
        ]);

        $region = \App\Models\Region::create([
            'country_id' => $country->id,
            'name_en' => 'Addis Ababa',
            'name_am' => 'አዲስ አበባ',
            'name_om' => 'Finfinnee',
            'status' => 'active'
        ]);

        $this->assertEquals('Ethiopia', $region->country->name_en);
        $this->assertEquals('አዲስ አበባ', $region->name_am);
    }

    public function test_location_filtering_and_api()
    {
        $user = \App\Models\User::factory()->create();
        $token = \Illuminate\Support\Str::random(40);
        \App\Models\ApiToken::create(['user_id' => $user->id, 'name' => 'test', 'token' => hash('sha256', $token)]);

        $country = \App\Models\Country::create(['code' => 'ET', 'name_en' => 'Ethiopia']);
        $region1 = \App\Models\Region::create(['country_id' => $country->id, 'name_en' => 'Oromia']);
        $region2 = \App\Models\Region::create(['country_id' => $country->id, 'name_en' => 'Amhara']);
        
        $zone1 = \App\Models\Zone::create(['region_id' => $region1->id, 'name_en' => 'Shewa']);
        $zone2 = \App\Models\Zone::create(['region_id' => $region2->id, 'name_en' => 'Gojjam']);

        $response = $this->withToken($token)->getJson('/api/v1/locations/zones?region_id=' . $region1->id);
        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $response->assertJsonPath('0.name_en', 'Shewa');
    }

    public function test_admin_can_create_location()
    {
        $user = \App\Models\User::factory()->create();
        $role = \App\Models\Role::create(['name' => 'Administrator']);
        $permission = \App\Models\Permission::create(['code' => 'manage_locations']);
        $role->permissions()->attach($permission->id);
        $user->assignRole($role);

        $token = \Illuminate\Support\Str::random(40);
        \App\Models\ApiToken::create(['user_id' => $user->id, 'name' => 'test', 'token' => hash('sha256', $token)]);

        $country = \App\Models\Country::create(['code' => 'ET', 'name_en' => 'Ethiopia']);

        $response = $this->withToken($token)->postJson('/api/v1/locations/regions', [
            'country_id' => $country->id,
            'name_en' => 'Sidama',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('regions', ['name_en' => 'Sidama']);
    }

    public function test_unauthorized_cannot_create_location()
    {
        $user = \App\Models\User::factory()->create();
        $token = \Illuminate\Support\Str::random(40);
        \App\Models\ApiToken::create(['user_id' => $user->id, 'name' => 'test', 'token' => hash('sha256', $token)]);

        $country = \App\Models\Country::create(['code' => 'ET', 'name_en' => 'Ethiopia']);

        $response = $this->withToken($token)->postJson('/api/v1/locations/regions', [
            'country_id' => $country->id,
            'name_en' => 'Sidama',
        ]);

        $response->assertStatus(403);
    }
}
