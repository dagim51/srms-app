<?php

namespace Tests\Feature;

use App\Models\ApiToken;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Tests\TestCase;

class FavoritesAndCollectionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_favorite_property_and_avoid_duplicates()
    {
        $user = User::factory()->create();
        $property = Property::create([
            'title' => 'Favorite Property',
            'property_code' => 'FAV-001',
            'owner_user_id' => User::factory()->create()->id,
        ]);

        $token = Str::random(40);
        ApiToken::create(['user_id' => $user->id, 'name' => 'test_token', 'token' => hash('sha256', $token)]);

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
        $user = User::factory()->create();
        $token = Str::random(40);
        ApiToken::create(['user_id' => $user->id, 'name' => 'test_token', 'token' => hash('sha256', $token)]);

        // Create collection
        $response = $this->withToken($token)->postJson('/api/v1/collections', [
            'name' => 'Dream Homes',
            'is_private' => true,
        ]);
        $response->assertStatus(201);
        $collectionId = $response->json('data.id');

        $property = Property::create([
            'title' => 'Collection Property',
            'property_code' => 'COL-001',
            'owner_user_id' => User::factory()->create()->id,
        ]);

        // Add property to collection
        $addResponse = $this->withToken($token)->postJson("/api/v1/collections/{$collectionId}/properties", [
            'property_id' => $property->id,
        ]);
        $addResponse->assertStatus(201);

        // Another user cannot view private collection
        Auth::logout(); // Clear stateful auth from previous request

        $user2 = User::factory()->create();
        $token2 = Str::random(40);
        ApiToken::create(['user_id' => $user2->id, 'name' => 'test_token2', 'token' => hash('sha256', $token2)]);

        $failResponse = $this->withToken($token2)->getJson("/api/v1/collections/{$collectionId}");
        if ($failResponse->status() !== 403) {
            dump($failResponse->json());
        }
        $failResponse->assertStatus(403);
    }
}
