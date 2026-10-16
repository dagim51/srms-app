<?php

namespace Tests\Feature;

use App\Models\Collection;
use App\Models\CollectionProperty;
use App\Models\Favorite;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoritesAndCollectionsWebTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_favorites_page_with_saved_properties()
    {
        $user = User::factory()->create();
        $property = Property::create([
            'title' => 'Penthouse in Kazanchis',
            'property_code' => 'PROP-FAV-01',
            'owner_user_id' => $user->id,
        ]);
        $property->listings()->create([
            'purpose' => 'For Sale',
            'price' => 25000000,
            'status' => 'published',
            'visibility' => 'public',
            'owner_actor_id' => $user->id,
        ]);

        Favorite::create([
            'user_id' => $user->id,
            'property_id' => $property->id,
        ]);

        $response = $this->actingAs($user)->get('/dashboard/favorites');
        $response->assertStatus(200);
        $response->assertSee('Penthouse in Kazanchis');
        $response->assertViewHas('favorites');
    }

    public function test_favorites_page_renders_useful_empty_state_when_no_favorites()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard/favorites');
        $response->assertStatus(200);
        $response->assertSee('No Saved Favorites Yet');
        $response->assertSee('Explore Properties');
    }

    public function test_public_favorites_alias_route_works()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/favorites');
        $response->assertStatus(200);
        $response->assertSee('Saved Favorites');
    }

    public function test_favorite_toggle_action_adds_and_removes_correctly()
    {
        $user = User::factory()->create();
        $property = Property::create([
            'title' => 'Villa in Bole',
            'property_code' => 'PROP-TOG-01',
            'owner_user_id' => $user->id,
        ]);

        // 1. Toggle ON: should add to favorites
        $responseOn = $this->actingAs($user)->postJson("/favorites/toggle/{$property->id}");
        $responseOn->assertStatus(200);
        $responseOn->assertJson([
            'favorited' => true,
            'count' => 1,
        ]);
        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'property_id' => $property->id,
        ]);

        // 2. Toggle OFF: should remove from favorites
        $responseOff = $this->actingAs($user)->postJson("/favorites/toggle/{$property->id}");
        $responseOff->assertStatus(200);
        $responseOff->assertJson([
            'favorited' => false,
            'count' => 0,
        ]);
        $this->assertDatabaseMissing('favorites', [
            'user_id' => $user->id,
            'property_id' => $property->id,
        ]);
    }

    public function test_duplicate_favorites_are_strictly_prevented()
    {
        $user = User::factory()->create();
        $property = Property::create([
            'title' => 'Unique Favorite Property',
            'property_code' => 'PROP-DUP-01',
            'owner_user_id' => $user->id,
        ]);

        Favorite::create([
            'user_id' => $user->id,
            'property_id' => $property->id,
        ]);

        // Attempting to create duplicate directly with firstOrCreate or database constraint
        $fav2 = Favorite::firstOrCreate([
            'user_id' => $user->id,
            'property_id' => $property->id,
        ]);

        $this->assertDatabaseCount('favorites', 1);
    }

    public function test_user_can_remove_favorite_via_delete_action()
    {
        $user = User::factory()->create();
        $property = Property::create([
            'title' => 'Removable Property',
            'property_code' => 'PROP-REM-01',
            'owner_user_id' => $user->id,
        ]);

        $fav = Favorite::create([
            'user_id' => $user->id,
            'property_id' => $property->id,
        ]);

        $response = $this->actingAs($user)->delete("/dashboard/favorites/{$fav->id}");
        $response->assertStatus(302);
        $this->assertDatabaseMissing('favorites', ['id' => $fav->id]);
    }

    public function test_collections_index_renders_successfully()
    {
        $user = User::factory()->create();
        $collection = Collection::create([
            'user_id' => $user->id,
            'name' => 'Dream Investments',
            'is_private' => true,
        ]);

        $response = $this->actingAs($user)->get('/dashboard/collections');
        $response->assertStatus(200);
        $response->assertSee('Dream Investments');
        $response->assertSee('Private');
    }

    public function test_user_can_create_new_collection()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/dashboard/collections', [
            'name' => 'Bole Villas 2026',
            'is_private' => 1,
        ]);

        $this->assertDatabaseHas('collections', [
            'user_id' => $user->id,
            'name' => 'Bole Villas 2026',
            'is_private' => 1,
        ]);
    }

    public function test_user_can_add_property_to_collection()
    {
        $user = User::factory()->create();
        $collection = Collection::create([
            'user_id' => $user->id,
            'name' => 'Vacation Homes',
            'is_private' => false,
        ]);

        $property = Property::create([
            'title' => 'Lakeview Resort House',
            'property_code' => 'PROP-LAKE-01',
            'owner_user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->postJson("/dashboard/collections/{$collection->id}/properties", [
            'property_id' => $property->id,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('collection_properties', [
            'collection_id' => $collection->id,
            'property_id' => $property->id,
        ]);
    }

    public function test_user_can_remove_property_from_collection()
    {
        $user = User::factory()->create();
        $collection = Collection::create([
            'user_id' => $user->id,
            'name' => 'Curated List',
            'is_private' => true,
        ]);

        $property = Property::create([
            'title' => 'Temporary Collection Item',
            'property_code' => 'PROP-TMP-01',
            'owner_user_id' => $user->id,
        ]);

        CollectionProperty::create([
            'collection_id' => $collection->id,
            'property_id' => $property->id,
        ]);

        $response = $this->actingAs($user)->deleteJson("/dashboard/collections/{$collection->id}/properties/{$property->id}");
        $response->assertStatus(200);
        $this->assertDatabaseMissing('collection_properties', [
            'collection_id' => $collection->id,
            'property_id' => $property->id,
        ]);
    }

    public function test_user_can_move_property_between_collections()
    {
        $user = User::factory()->create();
        $colA = Collection::create([
            'user_id' => $user->id,
            'name' => 'Collection A',
            'is_private' => true,
        ]);
        $colB = Collection::create([
            'user_id' => $user->id,
            'name' => 'Collection B',
            'is_private' => true,
        ]);

        $property = Property::create([
            'title' => 'Mobile Property',
            'property_code' => 'PROP-MOV-01',
            'owner_user_id' => $user->id,
        ]);

        CollectionProperty::create([
            'collection_id' => $colA->id,
            'property_id' => $property->id,
        ]);

        $response = $this->actingAs($user)->postJson("/dashboard/collections/{$colA->id}/properties/{$property->id}/move", [
            'target_collection_id' => $colB->id,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('collection_properties', [
            'collection_id' => $colA->id,
            'property_id' => $property->id,
        ]);
        $this->assertDatabaseHas('collection_properties', [
            'collection_id' => $colB->id,
            'property_id' => $property->id,
        ]);
    }

    public function test_private_collections_are_isolated_per_user()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $privateCollection = Collection::create([
            'user_id' => $user1->id,
            'name' => 'Confidential High-Net-Worth Portfolio',
            'is_private' => true,
        ]);

        // User 1 (owner) can view
        $resOwner = $this->actingAs($user1)->get("/dashboard/collections/{$privateCollection->id}");
        $resOwner->assertStatus(200);
        $resOwner->assertSee('Confidential High-Net-Worth Portfolio');

        // User 2 cannot view private collection of User 1 -> 403 Forbidden
        $resOther = $this->actingAs($user2)->get("/dashboard/collections/{$privateCollection->id}");
        $resOther->assertStatus(403);
    }
}
