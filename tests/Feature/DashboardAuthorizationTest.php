<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class DashboardAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Representative authenticated GET endpoints across the whole dashboard.
     */
    public static function protectedGetRoutes(): array
    {
        return [
            ['/dashboard'],
            ['/dashboard/favorites'],
            ['/favorites'],
            ['/dashboard/collections'],
            ['/dashboard/messages'],
            ['/dashboard/viewings'],
            ['/dashboard/offers'],
            ['/dashboard/reservations'],
            ['/dashboard/accommodations'],
            ['/dashboard/reviews'],
            ['/dashboard/notifications'],
            ['/dashboard/preferences'],
            ['/dashboard/organization'],
            ['/dashboard/crm'],
            ['/dashboard/projects'],
            ['/dashboard/sales'],
            ['/dashboard/rental'],
            ['/dashboard/operations'],
            ['/dashboard/finance'],
            ['/dashboard/documents'],
            ['/dashboard/trust'],
            ['/dashboard/admin'],
            ['/dashboard/admin/users'],
            ['/dashboard/users'],
            ['/dashboard/settings'],
            ['/dashboard/analytics'],
            ['/dashboard/alerts'],
            ['/dashboard/recommendations'],
            ['/dashboard/map'],
            ['/dashboard/intelligence/valuation'],
            ['/dashboard/services'],
            ['/dashboard/integrations'],
            ['/dashboard/ai/search'],
            ['/dashboard/listings'],
            ['/dashboard/amenities'],
            ['/dashboard/features'],
            ['/dashboard/properties'],
            ['/dashboard/properties/map'],
        ];
    }

    #[DataProvider('protectedGetRoutes')]
    public function test_guests_are_redirected_to_login_for_protected_dashboard_routes(string $route): void
    {
        $response = $this->get($route);

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    public function test_guest_cannot_access_state_changing_dashboard_endpoints(): void
    {
        // No authentication: every one of these must bounce to the login screen
        // instead of mutating data (previously they impersonated the first user).
        $this->post('/dashboard/collections', ['name' => 'Attackers Collection'])
            ->assertStatus(302)
            ->assertRedirect('/login');

        $this->post('/dashboard/reservations', [])
            ->assertStatus(302)
            ->assertRedirect('/login');

        $this->post('/dashboard/viewings', [])
            ->assertStatus(302)
            ->assertRedirect('/login');

        $this->post('/dashboard/offers', [])
            ->assertStatus(302)
            ->assertRedirect('/login');

        $this->post('/dashboard/listings', [])
            ->assertStatus(302)
            ->assertRedirect('/login');
    }

    public function test_guest_cannot_toggle_a_favorite(): void
    {
        $property = Property::create([
            'title' => 'Auth Test Property',
            'property_code' => 'AUTH-TEST-001',
            'owner_user_id' => User::factory()->create()->id,
            'status' => 'verified',
        ]);

        $this->post("/favorites/toggle/{$property->id}")
            ->assertStatus(302)
            ->assertRedirect('/login');
    }

    public function test_authenticated_user_can_access_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/dashboard')->assertStatus(200);
    }

    public function test_public_routes_remain_accessible_without_authentication(): void
    {
        foreach (['/', '/search', '/properties', '/developers', '/services'] as $route) {
            $this->get($route)->assertStatus(200);
        }
    }
}
