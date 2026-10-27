<?php

namespace Tests\Feature;

use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RealtimeChatEventTest extends TestCase
{
    use RefreshDatabase;

    public function test_message_sent_event_broadcasts_on_chat_channel(): void
    {
        $sender = User::factory()->create(['name' => 'Sara Alem']);
        $receiver = User::factory()->create(['name' => 'Dawit Tadesse']);

        $conversation = Conversation::create();

        ConversationParticipant::create([
            'conversation_id' => $conversation->id,
            'user_id' => $sender->id,
        ]);

        ConversationParticipant::create([
            'conversation_id' => $conversation->id,
            'user_id' => $receiver->id,
        ]);

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $sender->id,
            'body' => 'Hello from Bole Addis!',
        ]);

        $event = new MessageSent($message);

        $channels = $event->broadcastOn();
        $this->assertCount(1, $channels);
        $this->assertEquals('private-chat.'.$conversation->id, $channels[0]->name);
        $this->assertEquals('message.sent', $event->broadcastAs());

        $payload = $event->broadcastWith();
        $this->assertEquals($message->id, $payload['id']);
        $this->assertEquals('Hello from Bole Addis!', $payload['body']);
        $this->assertEquals($sender->id, $payload['sender_id']);
        $this->assertEquals('Sara Alem', $payload['sender']['name']);
    }

    public function test_storing_message_via_controller_dispatches_broadcast_event(): void
    {
        Event::fake([MessageSent::class]);

        $sender = User::factory()->create();
        $receiver = User::factory()->create();

        $conversation = Conversation::create();

        ConversationParticipant::create([
            'conversation_id' => $conversation->id,
            'user_id' => $sender->id,
        ]);

        ConversationParticipant::create([
            'conversation_id' => $conversation->id,
            'user_id' => $receiver->id,
        ]);

        $response = $this->actingAs($sender)->post(
            route('dashboard.messages.store', ['conversation' => $conversation->id]),
            ['body' => 'Testing real-time WebSocket dispatch']
        );

        $response->assertRedirect();

        Event::assertDispatched(MessageSent::class, function ($event) use ($conversation) {
            return $event->message->conversation_id === $conversation->id
                && $event->message->body === 'Testing real-time WebSocket dispatch';
        });
    }

    public function test_message_can_be_sent_with_file_attachment(): void
    {
        Storage::fake('public');

        $sender = User::factory()->create();
        $receiver = User::factory()->create();
        $conversation = Conversation::create();

        ConversationParticipant::create([
            'conversation_id' => $conversation->id,
            'user_id' => $sender->id,
        ]);
        ConversationParticipant::create([
            'conversation_id' => $conversation->id,
            'user_id' => $receiver->id,
        ]);

        $file = UploadedFile::fake()->create('property_floorplan.pdf', 500, 'application/pdf');

        $response = $this->actingAs($sender)->post(
            route('dashboard.messages.store', ['conversation' => $conversation->id]),
            [
                'body' => 'Here is the floor plan attachment.',
                'attachment' => $file,
            ]
        );

        $response->assertRedirect();

        $message = Message::where('conversation_id', $conversation->id)->latest()->first();
        $this->assertNotNull($message);
        $this->assertCount(1, $message->attachments);
        $this->assertEquals('property_floorplan.pdf', $message->attachments->first()->file_name);
        Storage::disk('public')->assertExists($message->attachments->first()->file_path);
    }

    public function test_offensive_message_can_be_reported_creating_user_flag(): void
    {
        $sender = User::factory()->create();
        $reporter = User::factory()->create();
        $conversation = Conversation::create();

        ConversationParticipant::create([
            'conversation_id' => $conversation->id,
            'user_id' => $sender->id,
        ]);
        ConversationParticipant::create([
            'conversation_id' => $conversation->id,
            'user_id' => $reporter->id,
        ]);

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $sender->id,
            'body' => 'Inappropriate scam offer outside platform.',
        ]);

        $response = $this->actingAs($reporter)->post(
            route('dashboard.messages.report', ['message' => $message->id]),
            [
                'reason' => 'fraud_scam',
                'description' => 'User asked for unverified bank wire transfer.',
            ]
        );

        $response->assertRedirect();

        $this->assertDatabaseHas('user_flags', [
            'user_id' => $sender->id,
            'reporter_id' => $reporter->id,
            'reason' => 'fraud_scam',
            'context_type' => Message::class,
            'context_id' => $message->id,
            'status' => 'pending',
        ]);
    }
}
