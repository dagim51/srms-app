<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\NewDeviceLoginNotification;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Tests\TestCase;

class Module1IdentityTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_sends_notification_for_registered_user(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'alice@example.com',
        ]);

        $response = $this->post('/forgot-password', [
            'email' => 'alice@example.com',
        ]);

        $response->assertSessionHas('status');
        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => 'alice@example.com',
        ]);

        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }

    public function test_forgot_password_does_not_reveal_unregistered_email(): void
    {
        Notification::fake();

        $response = $this->post('/forgot-password', [
            'email' => 'nonexistent@example.com',
        ]);

        $response->assertSessionHas('status');
        Notification::assertNothingSent();
    }

    public function test_reset_password_screen_renders_with_token(): void
    {
        $response = $this->get('/reset-password/sample-test-token?email=alice@example.com');
        $response->assertStatus(200);
        $response->assertSee('sample-test-token');
    }

    public function test_reset_password_succeeds_with_valid_token(): void
    {
        $user = User::factory()->create([
            'email' => 'alice@example.com',
            'password' => Hash::make('old-password'),
        ]);

        $rawToken = Str::random(64);
        DB::table('password_reset_tokens')->insert([
            'email' => 'alice@example.com',
            'token' => Hash::make($rawToken),
            'created_at' => now(),
        ]);

        $response = $this->post('/reset-password', [
            'token' => $rawToken,
            'email' => 'alice@example.com',
            'password' => 'new-secure-password',
            'password_confirmation' => 'new-secure-password',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHas('status');

        $user->refresh();
        $this->assertTrue(Hash::check('new-secure-password', $user->password));
        $this->assertDatabaseMissing('password_reset_tokens', [
            'email' => 'alice@example.com',
        ]);
    }

    public function test_reset_password_fails_with_invalid_token(): void
    {
        $user = User::factory()->create([
            'email' => 'alice@example.com',
        ]);

        DB::table('password_reset_tokens')->insert([
            'email' => 'alice@example.com',
            'token' => Hash::make('valid-token'),
            'created_at' => now(),
        ]);

        $response = $this->from('/reset-password/invalid-token')->post('/reset-password', [
            'token' => 'invalid-token',
            'email' => 'alice@example.com',
            'password' => 'new-secure-password',
            'password_confirmation' => 'new-secure-password',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_reset_password_fails_when_token_is_expired(): void
    {
        $user = User::factory()->create([
            'email' => 'alice@example.com',
        ]);

        $rawToken = Str::random(64);
        DB::table('password_reset_tokens')->insert([
            'email' => 'alice@example.com',
            'token' => Hash::make($rawToken),
            'created_at' => Carbon::now()->subMinutes(61),
        ]);

        $response = $this->from('/reset-password/'.$rawToken)->post('/reset-password', [
            'token' => $rawToken,
            'email' => 'alice@example.com',
            'password' => 'new-secure-password',
            'password_confirmation' => 'new-secure-password',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_login_listener_records_successful_login_and_trusted_device(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'bob@example.com',
        ]);

        event(new Login('web', $user, false));

        $this->assertDatabaseHas('login_events', [
            'user_id' => $user->id,
            'event_type' => 'login_success',
            'success' => true,
        ]);

        $this->assertDatabaseHas('trusted_devices', [
            'user_id' => $user->id,
        ]);

        Notification::assertSentTo($user, NewDeviceLoginNotification::class);
    }

    public function test_subsequent_login_from_same_device_does_not_resend_alert(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'bob@example.com',
        ]);

        // First login
        event(new Login('web', $user, false));
        Notification::assertSentToTimes($user, NewDeviceLoginNotification::class, 1);

        // Second login from same environment/device
        event(new Login('web', $user, false));
        // Notification count should remain 1
        Notification::assertSentToTimes($user, NewDeviceLoginNotification::class, 1);
    }

    public function test_login_listener_records_failed_login_event(): void
    {
        $user = User::factory()->create([
            'email' => 'target@example.com',
        ]);

        event(new Failed('web', $user, ['email' => 'target@example.com', 'password' => 'wrong']));

        $this->assertDatabaseHas('login_events', [
            'user_id' => $user->id,
            'event_type' => 'login_failed',
            'success' => false,
        ]);
    }
}
