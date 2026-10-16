<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GisMapExplorerWebTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_access_gis_map_explorer()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard/properties/map');
        $response->assertStatus(200);

        // Core UI elements
        $response->assertSee('id="gis_explorer_map"', false);
        $response->assertSee('x-data="gisMapExplorer"', false);
        $response->assertSee('Urban Areas:');
        $response->assertSee('Bole Atlas');
        $response->assertSee('Kazanchis');
        $response->assertSee('Old Airport');
        $response->assertSee('Nearby Properties');

        // Leaflet assets
        $response->assertSee('leaflet.css');
        $response->assertSee('leaflet.js');
    }

    public function test_dashboard_map_route_renders_gis_map_explorer()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard/map');
        $response->assertStatus(200);
        $response->assertSee('id="gis_explorer_map"', false);
        $response->assertSee('x-data="gisMapExplorer"', false);
    }

    public function test_radius_search_api_endpoint_returns_json()
    {
        $response = $this->getJson('/api/v1/properties/search/radius?latitude=9.0105&longitude=38.7612&radius=15');
        $response->assertStatus(200);
        $response->assertJsonStructure(['data']);
    }
}
