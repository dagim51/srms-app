<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PropertyMediaTest extends TestCase
{
    use RefreshDatabase;

    public function test_supported_media_can_be_uploaded()
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        \Illuminate\Support\Facades\Queue::fake();

        $owner = \App\Models\User::factory()->create();
        $token = \Illuminate\Support\Str::random(40);
        \App\Models\ApiToken::create(['user_id' => $owner->id, 'name' => 'test', 'token' => hash('sha256', $token)]);

        $property = \App\Models\Property::create([
            'title' => 'Test Property',
            'property_code' => 'TEST-001',
            'owner_user_id' => $owner->id,
            'status' => 'draft',
        ]);

        $file = \Illuminate\Http\Testing\File::image('photo.jpg');

        $response = $this->withToken($token)->postJson("/api/v1/properties/{$property->id}/media", [
            'file' => $file,
        ]);

        $response->assertStatus(201);
        $this->assertEquals('image', $response->json('kind'));
        
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($response->json('storage_key'));
        \Illuminate\Support\Facades\Queue::assertPushed(\App\Jobs\ProcessPropertyMedia::class);
    }

    public function test_unsupported_media_type_is_rejected()
    {
        $owner = \App\Models\User::factory()->create();
        $token = \Illuminate\Support\Str::random(40);
        \App\Models\ApiToken::create(['user_id' => $owner->id, 'name' => 'test', 'token' => hash('sha256', $token)]);

        $property = \App\Models\Property::create([
            'title' => 'Test Property',
            'property_code' => 'TEST-002',
            'owner_user_id' => $owner->id,
        ]);

        $file = \Illuminate\Http\Testing\File::create('document.pdf', 100, 'application/pdf');

        $response = $this->withToken($token)->postJson("/api/v1/properties/{$property->id}/media", [
            'file' => $file,
        ]);

        $response->assertStatus(422);
    }

    public function test_media_ordering_is_persisted()
    {
        $owner = \App\Models\User::factory()->create();
        $token = \Illuminate\Support\Str::random(40);
        \App\Models\ApiToken::create(['user_id' => $owner->id, 'name' => 'test', 'token' => hash('sha256', $token)]);

        $property = \App\Models\Property::create([
            'title' => 'Test Property',
            'property_code' => 'TEST-003',
            'owner_user_id' => $owner->id,
        ]);

        $media1 = $property->media()->create(['kind' => 'image', 'storage_key' => 'img1.jpg', 'sort_order' => 0]);
        $media2 = $property->media()->create(['kind' => 'image', 'storage_key' => 'img2.jpg', 'sort_order' => 1]);

        $response = $this->withToken($token)->putJson("/api/v1/properties/{$property->id}/media/order", [
            'media' => [
                ['id' => $media1->id, 'sort_order' => 1],
                ['id' => $media2->id, 'sort_order' => 0],
            ]
        ]);

        $response->assertStatus(200);

        $this->assertEquals(1, $media1->fresh()->sort_order);
        $this->assertEquals(0, $media2->fresh()->sort_order);
    }

    public function test_unauthorized_user_cannot_delete_media()
    {
        $owner = \App\Models\User::factory()->create();
        $property = \App\Models\Property::create([
            'title' => 'Test Property',
            'property_code' => 'TEST-004',
            'owner_user_id' => $owner->id,
        ]);
        $media = $property->media()->create(['kind' => 'image', 'storage_key' => 'img.jpg']);

        $otherUser = \App\Models\User::factory()->create();
        $token = \Illuminate\Support\Str::random(40);
        \App\Models\ApiToken::create(['user_id' => $otherUser->id, 'name' => 'test', 'token' => hash('sha256', $token)]);

        $response = $this->withToken($token)->deleteJson("/api/v1/properties/{$property->id}/media/{$media->id}");
        $response->assertStatus(403);
    }
}
