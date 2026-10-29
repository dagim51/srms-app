<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageHeaderCardWebTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_do_not_contain_page_header_card()
    {
        $endpoints = ['/', '/search', '/properties', '/developers', '/services'];

        foreach ($endpoints as $endpoint) {
            $response = $this->get($endpoint);
            $response->assertStatus(200);
            $response->assertDontSee('id="page-header-card"', false);
        }
    }

    public function test_auth_login_and_register_pages_do_not_contain_page_header_card()
    {
        $loginResponse = $this->get('/login');
        $loginResponse->assertStatus(200);
        $loginResponse->assertDontSee('id="page-header-card"', false);

        $registerResponse = $this->get('/register');
        $registerResponse->assertStatus(200);
        $registerResponse->assertDontSee('id="page-header-card"', false);
    }

    public function test_dashboard_pages_do_not_contain_top_page_header_card()
    {
        $this->seed(\Database\Seeders\AuthorizationSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('Administrator');

        $dashboardEndpoints = [
            '/dashboard',
            '/dashboard/properties/map',
            '/dashboard/properties',
            '/dashboard/rental',
            '/dashboard/crm',
            '/dashboard/finance',
            '/dashboard/operations',
        ];

        foreach ($dashboardEndpoints as $endpoint) {
            $response = $this->actingAs($user)->get($endpoint);
            $response->assertStatus(200);
            $response->assertDontSee('id="page-header-card"', false);
        }
    }
}
