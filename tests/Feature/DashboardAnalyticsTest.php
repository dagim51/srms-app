<?php

namespace Tests\Feature;

use App\Models\Inquiry;
use App\Models\Listing;
use App\Models\Offer;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_redirected_from_dashboard()
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');

        $dataResponse = $this->getJson('/dashboard/analytics-data');
        $dataResponse->assertStatus(401);
    }

    public function test_authenticated_user_can_view_analytics_dashboard()
    {
        $user = User::factory()->create();

        // Seed sample property & listing
        $property = Property::create([
            'owner_user_id' => $user->id,
            'title' => 'Bole Commercial Complex',
            'property_code' => 'BOLE-COM-01',
        ]);

        $listing = Listing::create([
            'property_id' => $property->id,
            'status' => 'published',
            'visibility' => 'public',
            'purpose' => 'For Sale',
            'price' => 15000000,
            'published_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Operational Overview &amp; Analytics', false);
        $response->assertSee('Active Listings');
        $response->assertSee('Portfolio Value');
        $response->assertSee('SRMS Property Transaction &amp; Moderation Lifecycle', false);
        $response->assertSee('dashboardTrendChart');
        $response->assertSee('dashboardPurposeChart');
        $response->assertSee('dashboardOutcomeChart');

        // Ensure no page-header-card id
        $response->assertDontSee('id="page-header-card"', false);
    }

    public function test_analytics_data_endpoint_returns_valid_json_structure()
    {
        $user = User::factory()->create();

        $property = Property::create([
            'owner_user_id' => $user->id,
            'title' => 'Kazanchis Luxury Residence',
            'property_code' => 'KAZ-RES-01',
        ]);

        $listing = Listing::create([
            'property_id' => $property->id,
            'status' => 'published',
            'visibility' => 'public',
            'purpose' => 'For Rent',
            'price' => 65000,
            'published_at' => now(),
        ]);

        Inquiry::create([
            'property_id' => $property->id,
            'listing_id' => $listing->id,
            'requester_id' => $user->id,
            'status' => 'pending',
            'message' => 'Interested in leasing terms',
        ]);

        Offer::create([
            'listing_id' => $listing->id,
            'buyer_id' => $user->id,
            'amount' => 60000,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->getJson('/dashboard/analytics-data?period=month&purpose=rent');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'filters',
            'metrics' => [
                'active_listings',
                'portfolio_value',
                'inquiries',
                'viewings',
                'offers',
                'revenue',
            ],
            'trends' => [
                'labels',
                'datasets',
            ],
            'purposeDist' => [
                'labels',
                'data',
                'colors',
            ],
            'offerOutcomes' => [
                'labels',
                'data',
                'colors',
            ],
        ]);

        $data = $response->json();
        $this->assertEquals('success', $data['status']);
        $this->assertGreaterThanOrEqual(1, $data['metrics']['inquiries']['pending']);
        $this->assertGreaterThanOrEqual(1, $data['metrics']['offers']['count']);
    }

    public function test_dashboard_supports_localization_without_errors()
    {
        $user = User::factory()->create();

        $responseAm = $this->actingAs($user)->withSession(['locale' => 'am'])->get('/dashboard');
        $responseAm->assertStatus(200);

        $responseOm = $this->actingAs($user)->withSession(['locale' => 'om'])->get('/dashboard');
        $responseOm->assertStatus(200);
    }
}
