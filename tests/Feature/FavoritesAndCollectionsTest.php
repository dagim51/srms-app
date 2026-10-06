<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class FavoritesAndCollectionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_favorite_property_and_avoid_duplicates()
    {
        $user = \App\Models\User::factory()->create();
        $property = \App\Models\Property::create([
            'title' => 'Favorite Property',
            'property_code' => 'FAV-001',
            'owner_user_id' => \App\Models\User::factory()->create()->id,
        ]);
        
        $token = \Illuminate\Support\Str::random(40);
        \App\Models\ApiToken::create(['user_id' => $user->id, 'name' => 'test_token', 'token' => hash('sha256', $token)]);

        // First favorite
        $response = $this->withToken($token)->postJson('/api/v1/favorites', ['property_id' => $property->id]);
        $response->assertStatus(201);
        $this->assertDatabaseCount('favorites', 1);

        // Duplicate favorite (should return 201 but not create duplicate)
        $response2 = $this->withToken($token)->postJson('/api/v1/favorites', ['property_id' => $property->id]);
        $response2->assertStatus(201);
        $this->assertDatabaseCount('favorites', 1);
    }

    public function test_user_can_manage_private_collection()
    {
        $user = \App\Models\User::factory()->create();
        $token = \Illuminate\Support\Str::random(40);
        \App\Models\ApiToken::create(['user_id' => $user->id, 'name' => 'test_token', 'token' => hash('sha256', $token)]);

        // Create collection
        $response = $this->withToken($token)->postJson('/api/v1/collections', [
            'name' => 'Dream Homes',
            'is_private' => true
        ]);
        $response->assertStatus(201);
        $collectionId = $response->json('data.id');

        $property = \App\Models\Property::create([
            'title' => 'Collection Property',
            'property_code' => 'COL-001',
            'owner_user_id' => \App\Models\User::factory()->create()->id,
        ]);

        // Add property to collection
        $addResponse = $this->withToken($token)->postJson("/api/v1/collections/{$collectionId}/properties", [
            'property_id' => $property->id
        ]);
        $addResponse->assertStatus(201);
        
        // Another user cannot view private collection
        \Illuminate\Support\Facades\Auth::logout(); // Clear stateful auth from previous request
        
        $user2 = \App\Models\User::factory()->create();
        $token2 = \Illuminate\Support\Str::random(40);
        \App\Models\ApiToken::create(['user_id' => $user2->id, 'name' => 'test_token2', 'token' => hash('sha256', $token2)]);
        
        $failResponse = $this->withToken($token2)->getJson("/api/v1/collections/{$collectionId}");
        if ($failResponse->status() !== 403) {
            dump($failResponse->json());
        }
        $failResponse->assertStatus(403);
    }
}
