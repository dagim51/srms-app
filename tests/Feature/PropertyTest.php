<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PropertyTest extends TestCase
{
    use RefreshDatabase;

    public function test_property_can_be_created_and_has_unique_identifier()
    {
        $user = \App\Models\User::factory()->create();
        $token = \Illuminate\Support\Str::random(40);
        \App\Models\ApiToken::create(['user_id' => $user->id, 'name' => 'test', 'token' => hash('sha256', $token)]);

        $response = $this->withToken($token)->postJson('/api/v1/properties', [
            'title' => 'Luxury Villa',
            'description' => 'Beautiful property',
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('data.title', 'Luxury Villa');
        $this->assertNotNull($response->json('data.property_code'));
        $this->assertEquals($user->id, $response->json('data.owner_user_id'));
    }

    public function test_unauthorized_user_cannot_edit_property()
    {
        $owner = \App\Models\User::factory()->create();
        $property = \App\Models\Property::create([
            'title' => 'Test Property',
            'property_code' => 'TEST-001',
            'owner_user_id' => $owner->id,
            'status' => 'draft',
        ]);

        $otherUser = \App\Models\User::factory()->create();
        $token = \Illuminate\Support\Str::random(40);
        \App\Models\ApiToken::create(['user_id' => $otherUser->id, 'name' => 'test', 'token' => hash('sha256', $token)]);

        $response = $this->withToken($token)->putJson('/api/v1/properties/' . $property->id, [
            'title' => 'Hacked Title',
        ]);

        // Laravel Gate returns 403 when unauthorized
        $response->assertStatus(403);
    }

    public function test_owner_can_edit_property_and_update_status()
    {
        $owner = \App\Models\User::factory()->create();
        $token = \Illuminate\Support\Str::random(40);
        \App\Models\ApiToken::create(['user_id' => $owner->id, 'name' => 'test', 'token' => hash('sha256', $token)]);

        $property = \App\Models\Property::create([
            'title' => 'Test Property',
            'property_code' => 'TEST-002',
            'owner_user_id' => $owner->id,
            'status' => 'draft',
        ]);

        $response = $this->withToken($token)->putJson('/api/v1/properties/' . $property->id, [
            'title' => 'Updated Title',
            'status' => 'pending_verification',
        ]);

        $response->assertStatus(200);
        $this->assertEquals('Updated Title', $response->json('data.title'));
        $this->assertEquals('pending_verification', $response->json('data.status'));
    }

    public function test_property_and_listing_are_separate_entities()
    {
        $owner = \App\Models\User::factory()->create();
        
        // A property can exist without a listing
        $property = \App\Models\Property::create([
            'title' => 'Master Asset',
            'property_code' => 'TEST-003',
            'owner_user_id' => $owner->id,
            'status' => 'verified',
        ]);

        $this->assertCount(0, $property->listings);

        // Multiple listings can reference the same property
        $listing1 = \App\Models\Listing::create([
            'property_id' => $property->id,
            'purpose' => 'For Sale',
            'price' => 5000000,
            'status' => 'published',
            'owner_actor_id' => $owner->id,
        ]);

        $listing2 = \App\Models\Listing::create([
            'property_id' => $property->id,
            'purpose' => 'For Rent',
            'price' => 20000,
            'status' => 'draft',
            'owner_actor_id' => $owner->id,
        ]);

        $property->refresh();

        $this->assertCount(2, $property->listings);
        $this->assertEquals('For Sale', $property->listings[0]->purpose);
        $this->assertEquals('For Rent', $property->listings[1]->purpose);
    }
}
