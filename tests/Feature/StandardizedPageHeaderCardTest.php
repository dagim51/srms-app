<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StandardizedPageHeaderCardTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $role = \App\Models\Role::firstOrCreate(['name' => 'Administrator']);
        \Illuminate\Support\Facades\DB::table('user_roles')->insert(['role_id' => $role->id, 'user_id' => $this->user->id]);
    }

    public function test_all_main_dashboard_pages_render_standardized_page_header_card()
    {
        $mainPages = [
            '/dashboard' => 'Dashboard',
            '/dashboard/properties' => 'Property Inventory',
            '/dashboard/properties/create' => 'Register New Property',
            '/dashboard/properties/map' => 'GIS Map Explorer',
            '/dashboard/listings' => 'Listings Management',
            '/dashboard/listings/create' => 'Publish New Listing',
            '/dashboard/moderation/listings' => 'Listing Moderation Queue',
            '/dashboard/favorites' => 'Saved Properties',
            '/dashboard/collections' => 'Curated Collections',
            '/dashboard/messages' => 'Inquiries &amp; Messaging',
            '/dashboard/viewings' => 'Property Viewings',
            '/dashboard/offers' => 'Offers &amp; Negotiations',
            '/dashboard/reservations' => 'Marketplace Reservations',
            '/dashboard/accommodations' => 'Short-Stay Accommodations',
            '/dashboard/amenities' => 'Amenity Catalog',
            '/dashboard/features' => 'Property Features Catalog',
            '/dashboard/settings' => 'System Settings &amp; Preferences',
            '/dashboard/rental' => 'Rental &amp; Lease Management',
            '/dashboard/crm' => 'Real Estate CRM &amp; Pipeline',
            '/dashboard/operations' => 'Operations &amp; Facilities',
            '/dashboard/finance' => 'Financial Accounting &amp; Ledgers',
            '/dashboard/analytics' => 'Reporting &amp; Analytics',
            '/dashboard/organization' => 'Organization &amp; Tenancy Management',
            '/dashboard/trust' => 'Trust &amp; Safety Center',
            '/dashboard/integrations' => 'External System Integrations',
            '/dashboard/documents' => 'Document Center',
            '/dashboard/projects' => 'Developer Projects',
            '/dashboard/alerts' => 'Saved Searches & Alerts',
            '/dashboard/ai/assistant' => 'AI Listing Assistant',
            '/dashboard/ai/search' => 'AI Property Search',
            '/dashboard/intelligence/calculators' => 'Investment Calculators',
            '/dashboard/intelligence/portfolio' => 'Portfolio Overview',
            '/dashboard/intelligence/valuation' => 'Valuation Tool',
            '/dashboard/services' => 'Professional Services',
            '/dashboard/services/provider' => 'Provider Dashboard',
            '/dashboard/notifications' => 'Notifications',
            '/dashboard/preferences' => 'Notification Preferences',
        ];

        foreach ($mainPages as $endpoint => $expectedTitle) {
            $response = $this->actingAs($this->user)->get($endpoint);

            $this->assertEquals(
                200, 
                $response->getStatusCode(), 
                "Endpoint {$endpoint} returned unexpected status code {$response->getStatusCode()}"
            );

            // Verify standardized component structure
            $response->assertSee('data-component="page-header"', false);
            $response->assertSee('page-header-card', false);
            $response->assertSee('#C2410C', false);
            $response->assertSee($expectedTitle, false);

            // Strictly ensure old problematic id is never rendered
            $response->assertDontSee('id="page-header-card"', false);
        }
    }

    public function test_page_header_component_supports_custom_props()
    {
        $view = $this->blade(
            '<x-page-header title="Custom Title" description="Custom Description" icon="building" badge="Verified" />'
        );

        $view->assertSee('Custom Title');
        $view->assertSee('Custom Description');
        $view->assertSee('Verified');
        $view->assertSee('#C2410C');
        $view->assertSee('data-component="page-header"', false);
    }
}
