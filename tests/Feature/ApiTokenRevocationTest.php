<?php

namespace Tests\Feature;

use App\Models\ApiToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class ApiTokenRevocationTest extends TestCase
{
    use RefreshDatabase;

    private function issueToken(User $user, array $overrides = []): string
    {
        $plainTextToken = Str::random(40);
        ApiToken::create(array_merge([
            'user_id' => $user->id,
            'name' => 'test',
            'token' => hash('sha256', $plainTextToken),
        ], $overrides));

        return $plainTextToken;
    }

    public function test_logout_revokes_only_the_token_that_was_used(): void
    {
        $user = User::factory()->create();
        $tokenA = $this->issueToken($user);
        $tokenB = $this->issueToken($user);

        $this->withToken($tokenA)->postJson('/api/v1/auth/logout')->assertStatus(204);

        // The token used for logout is revoked and no longer authenticates...
        $this->assertDatabaseMissing('api_tokens', ['token' => hash('sha256', $tokenA)]);
        $this->withToken($tokenA)->getJson('/api/v1/users/profile')->assertStatus(401);

        // ...but another device's token stays valid (logout is per-device).
        $this->assertDatabaseHas('api_tokens', ['token' => hash('sha256', $tokenB)]);
        $this->withToken($tokenB)->getJson('/api/v1/users/profile')->assertStatus(200);
    }

    public function test_password_reset_revokes_every_token_for_the_user(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-password-123'),
        ]);
        $tokenA = $this->issueToken($user);
        $tokenB = $this->issueToken($user);

        $forgotResponse = $this->postJson('/api/v1/auth/password/email', [
            'email' => $user->email,
        ])->assertStatus(200);
        $resetToken = $forgotResponse['token'];

        $this->postJson('/api/v1/auth/password/reset', [
            'email' => $user->email,
            'token' => $resetToken,
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])->assertStatus(200);

        // Every issued token is invalidated on password reset.
        $this->assertDatabaseMissing('api_tokens', ['user_id' => $user->id]);
        $this->withToken($tokenA)->getJson('/api/v1/users/profile')->assertStatus(401);
        $this->withToken($tokenB)->getJson('/api/v1/users/profile')->assertStatus(401);

        // The user can log in again with the new password.
        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'new-password-123',
        ])->assertStatus(200);
    }

    public function test_expired_token_is_rejected(): void
    {
        $user = User::factory()->create();
        $expiredToken = $this->issueToken($user, ['expires_at' => now()->subMinute()]);

        $this->withToken($expiredToken)->getJson('/api/v1/users/profile')->assertStatus(401);
    }

    public function test_unknown_token_is_rejected(): void
    {
        $this->withToken(Str::random(40))->getJson('/api/v1/users/profile')->assertStatus(401);
    }
}
