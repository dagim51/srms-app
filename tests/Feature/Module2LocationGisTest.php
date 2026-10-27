<?php

namespace Tests\Feature;

use App\Domains\Location\Models\Country;
use App\Domains\Location\Models\Region;
use App\Models\ListingSearchIndex;
use App\Services\GIS\AdvancedGIS;
use Database\Seeders\AuthorizationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Tests\TestCase;

class Module2LocationGisTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AuthorizationSeeder::class);
        Cache::flush();
    }

    public function test_afaan_oromo_translations_and_messages_load_properly(): void
    {
        App::setLocale('om');

        $this->assertEquals('Finfinnee (Addis Ababa)', __('locations.regions.addis_ababa'));
        $this->assertEquals('Oromiyaa', __('locations.regions.oromia'));
        $this->assertEquals('Boolee', __('locations.zones.bole'));

        $this->assertNotEmpty(__('messages.success'));
        $this->assertNotEmpty(__('messages.search_results'));

        App::setLocale('en');
    }

    public function test_advanced_gis_radius_search_returns_matches(): void
    {
        $gis = app(AdvancedGIS::class);

        // Center: Addis Ababa (8.9806, 38.7578)
        ListingSearchIndex::create([
            'listing_id' => (string) Str::uuid(),
            'title' => 'Bole Luxury Apartment',
            'latitude' => 8.9806,
            'longitude' => 38.7578,
            'price' => 2500000,
            'currency' => 'ETB',
            'purpose' => 'sale',
            'property_type_code' => 'apartment',
            'published_at' => now(),
        ]);

        // Far location: Hawassa (7.0504, 38.4682) ~275km away
        ListingSearchIndex::create([
            'listing_id' => (string) Str::uuid(),
            'title' => 'Hawassa Villa',
            'latitude' => 7.0504,
            'longitude' => 38.4682,
            'price' => 3500000,
            'currency' => 'ETB',
            'purpose' => 'sale',
            'property_type_code' => 'villa',
            'published_at' => now(),
        ]);

        // Search within 25km of Addis Ababa center
        $resultsNear = $gis->searchWithinRadius(8.9800, 38.7500, 25);
        $this->assertCount(1, $resultsNear);
        $this->assertEquals('Bole Luxury Apartment', $resultsNear->first()->title);

        // Search within 300km (should include Hawassa)
        $resultsFar = $gis->searchWithinRadius(8.9800, 38.7500, 300);
        $this->assertCount(2, $resultsFar);
    }

    public function test_advanced_gis_polygon_search_filters_within_bounding_box(): void
    {
        $gis = app(AdvancedGIS::class);

        ListingSearchIndex::create([
            'listing_id' => (string) Str::uuid(),
            'title' => 'Inside Polygon Property',
            'latitude' => 9.010,
            'longitude' => 38.760,
            'price' => 1500000,
            'currency' => 'ETB',
            'purpose' => 'rent',
            'property_type_code' => 'apartment',
            'published_at' => now(),
        ]);

        ListingSearchIndex::create([
            'listing_id' => (string) Str::uuid(),
            'title' => 'Outside Polygon Property',
            'latitude' => 10.500,
            'longitude' => 39.500,
            'price' => 2000000,
            'currency' => 'ETB',
            'purpose' => 'rent',
            'property_type_code' => 'commercial',
            'published_at' => now(),
        ]);

        $polygon = [
            [9.000, 38.750],
            [9.020, 38.750],
            [9.020, 38.770],
            [9.000, 38.770],
            [9.000, 38.750],
        ];

        $results = $gis->searchWithinPolygon($polygon);
        $this->assertCount(1, $results);
        $this->assertEquals('Inside Polygon Property', $results->first()->title);
    }

    public function test_advanced_gis_k_nearest_neighbors(): void
    {
        $gis = app(AdvancedGIS::class);

        ListingSearchIndex::create([
            'listing_id' => (string) Str::uuid(),
            'title' => 'Near Point A',
            'latitude' => 8.981,
            'longitude' => 38.758,
            'price' => 1000000,
            'currency' => 'ETB',
            'purpose' => 'sale',
            'property_type_code' => 'apartment',
            'published_at' => now(),
        ]);

        $results = $gis->kNearestNeighbors(8.980, 38.757, 5);
        $this->assertNotEmpty($results);
        $this->assertEquals('Near Point A', $results->first()->title);
    }

    public function test_location_model_supports_multilingual_attributes(): void
    {
        $country = Country::create([
            'name_en' => 'Ethiopia',
            'code' => 'ET',
        ]);

        $region = Region::create([
            'country_id' => $country->id,
            'name_en' => 'Oromia',
            'name_am' => 'ኦሮሚያ',
            'name_om' => 'Oromiyaa',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('regions', [
            'name_en' => 'Oromia',
            'name_om' => 'Oromiyaa',
        ]);

        $this->assertEquals('Oromiyaa', $region->name_om);
        $this->assertEquals('ኦሮሚያ', $region->name_am);
    }
}
