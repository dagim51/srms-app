<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class IdentityAndAuthTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_valid_registration()
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure(['data' => ['user', 'token']]);
        
        $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
        $this->assertDatabaseHas('api_tokens', ['user_id' => $response['data']['user']['id']]);
    }

    public function test_duplicate_registration_rejected()
    {
        \App\Models\User::factory()->create([
            'email' => 'jane@example.com'
        ]);

        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422);
    }

    public function test_invalid_login()
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'wrong@example.com',
            'password' => 'wrong',
        ]);

        $response->assertStatus(401);
    }

    public function test_successful_login_and_logout()
    {
        $user = \App\Models\User::factory()->create([
            'password' => \Illuminate\Support\Facades\Hash::make('password123')
        ]);

        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $loginResponse->assertStatus(200);
        $token = $loginResponse['data']['token'];

        $profileResponse = $this->withToken($token)->getJson('/api/v1/users/profile');
        $profileResponse->assertStatus(200);

        $logoutResponse = $this->withToken($token)->postJson('/api/v1/auth/logout');
        $logoutResponse->assertStatus(204);

        $this->assertDatabaseMissing('api_tokens', ['token' => hash('sha256', $token)]);
        
        $profileResponseAfter = $this->withToken($token)->getJson('/api/v1/users/profile');
        $profileResponseAfter->assertStatus(401);
    }

    public function test_unauthenticated_cannot_access_profile()
    {
        $response = $this->getJson('/api/v1/users/profile');
        $response->assertStatus(401);
    }

    public function test_password_reset_flow()
    {
        $user = \App\Models\User::factory()->create();

        $forgotResponse = $this->postJson('/api/v1/auth/password/email', [
            'email' => $user->email
        ]);
        
        $forgotResponse->assertStatus(200);
        $token = $forgotResponse['token'];

        $resetResponse = $this->postJson('/api/v1/auth/password/reset', [
            'email' => $user->email,
            'token' => $token,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $resetResponse->assertStatus(200);

        // Login with new password
        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'newpassword123',
        ])->assertStatus(200);
    }
}
