<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthThrottlingTest extends TestCase
{
    use RefreshDatabase;

    public function test_web_login_is_throttled_after_six_failed_attempts(): void
    {
        for ($i = 0; $i < 6; $i++) {
            $this->post('/login', [
                'email' => 'nobody@example.com',
                'password' => 'wrong-password',
            ])->assertStatus(302);
        }

        $this->post('/login', [
            'email' => 'nobody@example.com',
            'password' => 'wrong-password',
        ])->assertStatus(429);
    }

    public function test_web_register_is_throttled(): void
    {
        // Invalid payload (password confirmation mismatch) so no account is
        // created and the request is never authenticated; every attempt still
        // passes through the throttle middleware.
        $payload = [
            'name' => 'Throttle Test',
            'email' => 'throttle@example.com',
            'password' => 'password123',
            'password_confirmation' => 'does-not-match',
        ];

        for ($i = 0; $i < 6; $i++) {
            $this->post('/register', $payload)->assertStatus(302);
        }

        $this->post('/register', $payload)->assertStatus(429);
    }

    public function test_api_login_is_throttled_after_six_failed_attempts(): void
    {
        for ($i = 0; $i < 6; $i++) {
            $this->postJson('/api/v1/auth/login', [
                'email' => 'nobody@example.com',
                'password' => 'wrong-password',
            ])->assertStatus(401);
        }

        $this->postJson('/api/v1/auth/login', [
            'email' => 'nobody@example.com',
            'password' => 'wrong-password',
        ])->assertStatus(429);
    }

    public function test_api_group_rate_limiter_blocks_excessive_requests(): void
    {
        $user = User::factory()->create();

        // throttle:api allows 60 requests/minute for the whole API group.
        // actingAs() keeps the limiter key (the authenticated user's id) stable
        // across every request, so the budget accumulates to its cap.
        for ($i = 0; $i < 60; $i++) {
            $this->actingAs($user)->getJson('/api/v1/users/profile')->assertStatus(200);
        }

        $this->actingAs($user)->getJson('/api/v1/users/profile')->assertStatus(429);
    }

    public function test_webhook_endpoint_is_rate_limited_by_the_api_group(): void
    {
        // Webhooks sit inside routes/api.php, so the api group's throttle:api
        // (60/min) protects them too.
        for ($i = 0; $i < 60; $i++) {
            $this->postJson('/api/webhooks/test', ['type' => 'test.event'])->assertStatus(200);
        }

        $this->postJson('/api/webhooks/test', ['type' => 'test.event'])->assertStatus(429);
    }
}
