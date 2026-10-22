<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicDiscoveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_can_browse_published_listings()
    {
        $owner = User::factory()->create();
        $property = Property::create([
            'title' => 'Discovery Property',
            'property_code' => 'PUB-001',
            'owner_user_id' => $owner->id,
        ]);
        $listing = $property->listings()->create([
            'purpose' => 'For Sale',
            'price' => 5000000,
            'status' => 'published',
            'owner_actor_id' => $owner->id,
            'published_at' => now(),
        ]);

        $response = $this->getJson('/api/v1/public/listings');
        $response->assertStatus(200);
        $response->assertJsonFragment(['purpose' => 'For Sale']);
    }

    public function test_draft_listings_are_hidden_from_public()
    {
        $owner = User::factory()->create();
        $property = Property::create([
            'title' => 'Hidden Property',
            'property_code' => 'PUB-002',
            'owner_user_id' => $owner->id,
        ]);
        $listing = $property->listings()->create([
            'purpose' => 'For Rent',
            'price' => 5000,
            'status' => 'draft',
            'owner_actor_id' => $owner->id,
        ]);

        $response = $this->getJson('/api/v1/public/listings');
        $response->assertStatus(200);
        $response->assertJsonMissing(['purpose' => 'For Rent']);

        // Directly accessing detail page
        $responseDetail = $this->getJson("/api/v1/public/listings/{$listing->id}");
        $responseDetail->assertStatus(404);
    }

    public function test_listing_detail_hides_sensitive_documents()
    {
        $owner = User::factory()->create();
        $property = Property::create([
            'title' => 'Detailed Property',
            'property_code' => 'PUB-003',
            'owner_user_id' => $owner->id,
        ]);

        $property->media()->create([
            'kind' => 'document',
            'storage_key' => 'docs/title_deed.pdf',
            'visibility' => 'private',
        ]);

        $property->media()->create([
            'kind' => 'image',
            'storage_key' => 'images/front.jpg',
            'visibility' => 'public',
        ]);

        $listing = $property->listings()->create([
            'purpose' => 'For Sale',
            'price' => 120000,
            'status' => 'published',
            'owner_actor_id' => $owner->id,
            'published_at' => now(),
        ]);

        $response = $this->getJson("/api/v1/public/listings/{$listing->id}");
        $response->assertStatus(200);

        // PropertyResource uses 'gallery' for media collection
        $media = $response->json('data.property.gallery');
        $this->assertCount(1, $media);
        $this->assertEquals('public', $media[0]['visibility']);
    }
}
