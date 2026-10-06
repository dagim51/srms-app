<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Review;
use App\Models\Property;
use App\Models\Listing;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    protected $reviewer;
    protected $agent;
    protected $listing;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->reviewer = User::factory()->create();
        $this->agent = User::factory()->create();
        
        $property = Property::create([
            'title' => 'Test Property for Review',
            'property_code' => 'TEST-REV-001',
            'owner_user_id' => $this->agent->id,
            'status' => 'verified',
        ]);
        
        $this->listing = Listing::create([
            'property_id' => $property->id,
            'purpose' => 'For Sale',
            'price' => 5000000,
            'owner_actor_id' => $this->agent->id,
            'status' => 'published',
            'visibility' => 'public',
        ]);
    }

    public function test_users_can_review_listings_and_agents()
    {
        // Review a listing
        $listingResponse = $this->actingAs($this->reviewer)->postJson('/api/v1/reviews', [
            'reviewable_type' => 'App\Models\Listing',
            'reviewable_id' => $this->listing->id,
            'rating' => 5,
            'comment' => 'Great listing!',
        ]);
        
        $listingResponse->assertStatus(201);
        
        // Review an agent
        $agentResponse = $this->actingAs($this->reviewer)->postJson('/api/v1/reviews', [
            'reviewable_type' => 'App\Models\User',
            'reviewable_id' => $this->agent->id,
            'rating' => 4,
            'comment' => 'Helpful agent.',
        ]);
        
        $agentResponse->assertStatus(201);
    }

    public function test_multiple_reviews_from_same_user_prevented()
    {
        Review::create([
            'reviewer_id' => $this->reviewer->id,
            'reviewable_type' => 'App\Models\Listing',
            'reviewable_id' => $this->listing->id,
            'rating' => 5,
        ]);

        $response = $this->actingAs($this->reviewer)->postJson('/api/v1/reviews', [
            'reviewable_type' => 'App\Models\Listing',
            'reviewable_id' => $this->listing->id,
            'rating' => 1,
        ]);
        
        $response->assertStatus(409); // Conflict
    }

    public function test_users_cannot_review_themselves()
    {
        $response = $this->actingAs($this->agent)->postJson('/api/v1/reviews', [
            'reviewable_type' => 'App\Models\User',
            'reviewable_id' => $this->agent->id,
            'rating' => 5,
        ]);
        
        $response->assertStatus(422); // Unprocessable
    }

    public function test_inappropriate_reviews_can_be_reported()
    {
        $review = Review::create([
            'reviewer_id' => $this->reviewer->id,
            'reviewable_type' => 'App\Models\Listing',
            'reviewable_id' => $this->listing->id,
            'rating' => 1,
            'comment' => 'Terrible experience!',
        ]);

        $response = $this->actingAs($this->agent)->postJson("/api/v1/reviews/{$review->id}/report");
        
        $response->assertStatus(200);
        $this->assertEquals('flagged', $review->fresh()->status);
    }
}
