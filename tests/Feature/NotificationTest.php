<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\TestNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function test_notification_preferences_can_be_retrieved_and_updated()
    {
        $response = $this->actingAs($this->user)->getJson('/api/v1/notification-preferences');

        $response->assertStatus(200)
            ->assertJsonPath('data.email_notifications', true)
            ->assertJsonPath('data.in_app_notifications', true);

        $updateResponse = $this->actingAs($this->user)->putJson('/api/v1/notification-preferences', [
            'email_notifications' => false,
            'push_notifications' => true,
        ]);

        $updateResponse->assertStatus(200)
            ->assertJsonPath('data.email_notifications', false)
            ->assertJsonPath('data.push_notifications', true);
    }

    public function test_notifications_are_generated_and_can_be_marked_read()
    {
        $this->user->notify(new TestNotification);

        $this->assertCount(1, $this->user->notifications);
        $notificationId = $this->user->notifications->first()->id;

        $response = $this->actingAs($this->user)->getJson('/api/v1/notifications');
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.data');

        $markResponse = $this->actingAs($this->user)->postJson('/api/v1/notifications/mark-read', [
            'notification_ids' => [$notificationId],
        ]);

        $markResponse->assertStatus(200);

        $this->assertNotNull($this->user->fresh()->notifications->first()->read_at);
    }

    public function test_notifications_can_be_deleted()
    {
        $this->user->notify(new TestNotification);
        $notificationId = $this->user->notifications->first()->id;

        $response = $this->actingAs($this->user)->deleteJson("/api/v1/notifications/{$notificationId}");

        $response->assertStatus(204);
        $this->assertCount(0, $this->user->fresh()->notifications);
    }
}
