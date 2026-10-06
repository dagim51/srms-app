<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Property;
use App\Models\Listing;
use App\Models\Inquiry;
use App\Models\Conversation;
use App\Models\Message;

class InquiryAndMessagingTest extends TestCase
{
    use RefreshDatabase;

    protected $requester;
    protected $agent;
    protected $listing;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->requester = User::factory()->create();
        $this->agent = User::factory()->create();
        
        $property = Property::create([
            'title' => 'Test Property for Inquiry',
            'property_code' => 'TEST-INQ-001',
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

    public function test_authorized_inquiries_can_be_created()
    {
        $response = $this->actingAs($this->requester)->postJson('/api/v1/inquiries', [
            'listing_id' => $this->listing->id,
            'message' => 'I am interested in this property.',
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('data.status', 'pending');
                 
        $this->assertDatabaseHas('inquiries', [
            'requester_id' => $this->requester->id,
            'listing_id' => $this->listing->id,
        ]);
    }

    public function test_unauthorized_users_cannot_read_conversations()
    {
        $conversation = Conversation::create(['subject' => 'Test']);
        $conversation->participants()->create(['user_id' => $this->requester->id]);
        $conversation->participants()->create(['user_id' => $this->agent->id]);

        $randomUser = User::factory()->create();

        $response = $this->actingAs($randomUser)->getJson("/api/v1/conversations/{$conversation->id}");
        $response->assertStatus(403);
    }

    public function test_participants_can_read_conversations()
    {
        $conversation = Conversation::create(['subject' => 'Test']);
        $conversation->participants()->create(['user_id' => $this->requester->id]);
        
        $response = $this->actingAs($this->requester)->getJson("/api/v1/conversations/{$conversation->id}");
        $response->assertStatus(200);
    }

    public function test_messages_belong_to_correct_conversation_and_ownership_is_preserved()
    {
        $conversation = Conversation::create(['subject' => 'Test']);
        $conversation->participants()->create(['user_id' => $this->requester->id]);
        
        $response = $this->actingAs($this->requester)->postJson('/api/v1/messages', [
            'conversation_id' => $conversation->id,
            'body' => 'Hello there',
        ]);

        $response->assertStatus(201);
        
        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'sender_id' => $this->requester->id,
            'body' => 'Hello there',
        ]);
    }
    
    public function test_read_unread_behavior_works()
    {
        $conversation = Conversation::create(['subject' => 'Test']);
        $participant = $conversation->participants()->create(['user_id' => $this->requester->id]);
        
        $this->assertNull($participant->fresh()->last_read_at);
        
        $this->actingAs($this->requester)->getJson("/api/v1/conversations/{$conversation->id}");
        
        $this->assertNotNull($participant->fresh()->last_read_at);
    }
}
