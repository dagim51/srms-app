<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ListingModerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_seller_can_submit_listing()
    {
        $owner = \App\Models\User::factory()->create();
        $token = \Illuminate\Support\Str::random(40);
        \App\Models\ApiToken::create(['user_id' => $owner->id, 'name' => 'test', 'token' => hash('sha256', $token)]);

        $property = \App\Models\Property::create([
            'title' => 'Submission Property',
            'property_code' => 'MOD-001',
            'owner_user_id' => $owner->id,
        ]);

        $listing = $property->listings()->create([
            'purpose' => 'For Sale',
            'price' => 5000000,
            'status' => 'draft',
            'owner_actor_id' => $owner->id
        ]);

        $response = $this->withToken($token)->postJson("/api/v1/listings/{$listing->id}/submit");
        
        $response->assertStatus(200);
        $this->assertEquals('pending_review', $listing->fresh()->status);
    }

    public function test_moderator_can_approve_listing()
    {
        $owner = \App\Models\User::factory()->create();
        $property = \App\Models\Property::create([
            'title' => 'Moderation Property',
            'property_code' => 'MOD-002',
            'owner_user_id' => $owner->id,
        ]);
        $listing = $property->listings()->create([
            'purpose' => 'For Sale',
            'price' => 5000000,
            'status' => 'pending_review',
            'owner_actor_id' => $owner->id
        ]);

        $admin = \App\Models\User::factory()->create();
        $token = \Illuminate\Support\Str::random(40);
        \App\Models\ApiToken::create(['user_id' => $admin->id, 'name' => 'admin_token', 'token' => hash('sha256', $token)]);
        // Assign admin role
        $role = \App\Models\Role::firstOrCreate(['name' => 'Administrator']);
        \Illuminate\Support\Facades\DB::table('user_roles')->insert(['role_id' => $role->id, 'user_id' => $admin->id]);

        $response = $this->withToken($token)->postJson("/api/v1/moderation/listings/{$listing->id}/process", [
            'action' => 'approve'
        ]);

        $response->assertStatus(200);
        $this->assertEquals('approved', $listing->fresh()->status);
        
        $this->assertDatabaseHas('listing_moderations', [
            'listing_id' => $listing->id,
            'moderator_id' => $admin->id,
            'action' => 'approve'
        ]);
    }

    public function test_unauthorized_user_cannot_moderate()
    {
        $owner = \App\Models\User::factory()->create();
        $property = \App\Models\Property::create([
            'title' => 'Unauthorized Property',
            'property_code' => 'MOD-003',
            'owner_user_id' => $owner->id,
        ]);
        $listing = $property->listings()->create([
            'purpose' => 'For Rent',
            'price' => 2000,
            'status' => 'pending_review',
            'owner_actor_id' => $owner->id
        ]);

        $otherUser = \App\Models\User::factory()->create();
        $token = \Illuminate\Support\Str::random(40);
        \App\Models\ApiToken::create(['user_id' => $otherUser->id, 'name' => 'other_token', 'token' => hash('sha256', $token)]);
        // otherUser is NOT admin

        $response = $this->withToken($token)->postJson("/api/v1/moderation/listings/{$listing->id}/process", [
            'action' => 'approve'
        ]);

        $response->assertStatus(403);
    }
}
