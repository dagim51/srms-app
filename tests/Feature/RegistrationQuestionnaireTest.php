<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\AuthorizationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationQuestionnaireTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AuthorizationSeeder::class);
    }

    public function test_registration_view_renders_two_tier_stepper_and_categories_and_subtypes()
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertSee('Select Your Account Type');
        $response->assertSee('Seeking Property');
        $response->assertSee('Listing &amp; Monetizing', false);
        $response->assertSee('Real Estate Pro');

        // Subtypes check
        $response->assertSee('Property Buyer');
        $response->assertSee('Tenant / Renter');
        $response->assertSee('Short-Stay Guest');
        $response->assertSee('Property Seller / Owner');
        $response->assertSee('Landlord');
        $response->assertSee('Vacation Host');
        $response->assertSee('Real Estate Agent / Broker');
        $response->assertSee('Property Developer');
        $response->assertSee('Property Manager');
        $response->assertSee('Professional Services');
        $response->assertSee('Continue to Account Details');
    }

    public function test_registration_fails_without_intent()
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'terms' => 'on',
        ]);

        $response->assertSessionHasErrors(['intent']);
        $this->assertDatabaseMissing('users', ['email' => 'test@example.com']);
    }

    public function test_registration_fails_with_invalid_intent()
    {
        $response = $this->post('/register', [
            'intent' => 'hacker_role',
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'terms' => 'on',
        ]);

        $response->assertSessionHasErrors(['intent']);
        $this->assertDatabaseMissing('users', ['email' => 'test@example.com']);
    }

    public function test_buyer_registration_assigns_buyer_role()
    {
        $response = $this->post('/register', [
            'intent' => 'buyer',
            'name' => 'Abebe Buyer',
            'email' => 'buyer@example.com',
            'phone' => '+251911000001',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'terms' => 'on',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();

        $user = User::where('email', 'buyer@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('buyer', $user->intent);
        $this->assertEquals('+251911000001', $user->phone);
        $this->assertTrue($user->hasRole('Buyer'));
    }

    public function test_tenant_registration_assigns_tenant_role()
    {
        $response = $this->post('/register', [
            'intent' => 'tenant',
            'name' => 'Tigist Tenant',
            'email' => 'tenant@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'terms' => 'on',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();

        $user = User::where('email', 'tenant@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('tenant', $user->intent);
        $this->assertTrue($user->hasRole('Tenant'));
    }

    public function test_traveler_registration_assigns_guest_traveler_role()
    {
        $response = $this->post('/register', [
            'intent' => 'traveler',
            'name' => 'Sara Traveler',
            'email' => 'traveler@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'terms' => 'on',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();

        $user = User::where('email', 'traveler@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('traveler', $user->intent);
        $this->assertTrue($user->hasRole('Guest / Traveler'));
    }

    public function test_seller_registration_assigns_property_owner_role()
    {
        $response = $this->post('/register', [
            'intent' => 'seller',
            'name' => 'Dawit Seller',
            'email' => 'seller@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'terms' => 'on',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();

        $user = User::where('email', 'seller@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('seller', $user->intent);
        $this->assertTrue($user->hasRole('Property Owner'));
    }

    public function test_landlord_registration_assigns_landlord_role()
    {
        $response = $this->post('/register', [
            'intent' => 'landlord',
            'name' => 'Kebede Landlord',
            'email' => 'landlord@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'terms' => 'on',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();

        $user = User::where('email', 'landlord@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('landlord', $user->intent);
        $this->assertTrue($user->hasRole('Landlord'));
    }

    public function test_host_registration_assigns_host_role()
    {
        $response = $this->post('/register', [
            'intent' => 'host',
            'name' => 'Marta Host',
            'email' => 'host@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'terms' => 'on',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();

        $user = User::where('email', 'host@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('host', $user->intent);
        $this->assertTrue($user->hasRole('Host'));
    }

    public function test_agent_registration_assigns_agent_role()
    {
        $response = $this->post('/register', [
            'intent' => 'agent',
            'name' => 'Helen Agent',
            'email' => 'agent@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'terms' => 'on',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();

        $user = User::where('email', 'agent@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('agent', $user->intent);
        $this->assertTrue($user->hasRole('Agent'));
    }

    public function test_developer_registration_assigns_developer_role()
    {
        $response = $this->post('/register', [
            'intent' => 'developer',
            'name' => 'Ayat Real Estate',
            'email' => 'developer@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'terms' => 'on',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();

        $user = User::where('email', 'developer@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('developer', $user->intent);
        $this->assertTrue($user->hasRole('Developer'));
    }

    public function test_property_manager_registration_assigns_property_manager_role()
    {
        $response = $this->post('/register', [
            'intent' => 'property_manager',
            'name' => 'Yonas Manager',
            'email' => 'manager@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'terms' => 'on',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();

        $user = User::where('email', 'manager@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('property_manager', $user->intent);
        $this->assertTrue($user->hasRole('Property Manager'));
    }

    public function test_service_provider_registration_assigns_service_provider_role()
    {
        $response = $this->post('/register', [
            'intent' => 'service_provider',
            'name' => 'Berhanu Surveyor',
            'email' => 'surveyor@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'terms' => 'on',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();

        $user = User::where('email', 'surveyor@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('service_provider', $user->intent);
        $this->assertTrue($user->hasRole('Professional Service Provider'));
    }

    public function test_backward_compatibility_aliases()
    {
        $response = $this->post('/register', [
            'intent' => 'buyer_tenant',
            'name' => 'Legacy Buyer',
            'email' => 'legacybuyer@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'terms' => 'on',
        ]);
        $response->assertRedirect('/dashboard');
        $user = User::where('email', 'legacybuyer@example.com')->first();
        $this->assertTrue($user->hasRole('Buyer'));
    }

    public function test_dashboard_displays_personalized_welcome_for_tenant()
    {
        $user = User::factory()->create([
            'intent' => 'tenant',
        ]);
        $user->assignRole('Tenant');

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Tenant Portal');
        $response->assertSee('Manage Your Leases &amp; Rentals', false);
    }

    public function test_dashboard_displays_personalized_welcome_for_landlord()
    {
        $user = User::factory()->create([
            'intent' => 'landlord',
        ]);
        $user->assignRole('Landlord');

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Landlord Operations Hub');
        $response->assertSee('Manage Your Leases &amp;', false);
    }

    public function test_dashboard_displays_personalized_welcome_for_developer()
    {
        $user = User::factory()->create([
            'intent' => 'developer',
        ]);
        $user->assignRole('Developer');

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Real Estate Developer Suite');
        $response->assertSee('Master Development Projects');
    }
}
