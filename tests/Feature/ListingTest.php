<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ListingTest extends TestCase
{
    use RefreshDatabase;

    public function test_property_can_have_multiple_listings()
    {
        $owner = \App\Models\User::factory()->create();
        $token = \Illuminate\Support\Str::random(40);
        \App\Models\ApiToken::create(['user_id' => $owner->id, 'name' => 'test', 'token' => hash('sha256', $token)]);

        $property = \App\Models\Property::create([
            'title' => 'Multi Listing Property',
            'property_code' => 'LST-001',
            'owner_user_id' => $owner->id,
        ]);

        $this->withToken($token)->postJson("/api/v1/properties/{$property->id}/listings", [
            'purpose' => 'For Sale',
            'price' => 5000000
        ])->assertStatus(201);

        $this->withToken($token)->postJson("/api/v1/properties/{$property->id}/listings", [
            'purpose' => 'For Rent',
            'price' => 15000
        ])->assertStatus(201);

        $this->assertCount(2, $property->listings()->get());
    }

    public function test_listing_requires_mandatory_fields_to_publish()
    {
        $owner = \App\Models\User::factory()->create();
        $token = \Illuminate\Support\Str::random(40);
        \App\Models\ApiToken::create(['user_id' => $owner->id, 'name' => 'test', 'token' => hash('sha256', $token)]);

        $property = \App\Models\Property::create([
            'title' => 'Publishable Property',
            'property_code' => 'LST-002',
            'owner_user_id' => $owner->id,
        ]);

        $listing = $property->listings()->create([
            'purpose' => 'For Sale',
            'status' => 'draft',
            'owner_actor_id' => $owner->id
        ]); // Price is null

        $response = $this->withToken($token)->patchJson("/api/v1/listings/{$listing->id}/status", [
            'status' => 'published'
        ]);

        $response->assertStatus(422); // Validation fails because missing price
    }

    public function test_listing_can_be_published_with_valid_fields()
    {
        $owner = \App\Models\User::factory()->create();
        $token = \Illuminate\Support\Str::random(40);
        \App\Models\ApiToken::create(['user_id' => $owner->id, 'name' => 'test', 'token' => hash('sha256', $token)]);

        $property = \App\Models\Property::create([
            'title' => 'Valid Property',
            'property_code' => 'LST-003',
            'owner_user_id' => $owner->id,
        ]);

        $listing = $property->listings()->create([
            'purpose' => 'For Sale',
            'price' => 100000,
            'status' => 'draft',
            'owner_actor_id' => $owner->id
        ]);

        $response = $this->withToken($token)->patchJson("/api/v1/listings/{$listing->id}/status", [
            'status' => 'published'
        ]);

        $response->assertStatus(200);
        $this->assertEquals('published', $listing->fresh()->status);
        $this->assertNotNull($listing->fresh()->published_at);
    }

    public function test_unauthorized_user_cannot_alter_listing()
    {
        $owner = \App\Models\User::factory()->create();
        $property = \App\Models\Property::create([
            'title' => 'Locked Property',
            'property_code' => 'LST-004',
            'owner_user_id' => $owner->id,
        ]);
        $listing = $property->listings()->create([
            'purpose' => 'For Sale',
            'price' => 100000,
            'owner_actor_id' => $owner->id
        ]);

        $otherUser = \App\Models\User::factory()->create();
        $token = \Illuminate\Support\Str::random(40);
        \App\Models\ApiToken::create(['user_id' => $otherUser->id, 'name' => 'test', 'token' => hash('sha256', $token)]);

        $response = $this->withToken($token)->putJson("/api/v1/listings/{$listing->id}", [
            'price' => 200000
        ]);

        $response->assertStatus(403);
    }
}
