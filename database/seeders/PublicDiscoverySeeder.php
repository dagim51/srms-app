<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Property;
use App\Models\Listing;

class PublicDiscoverySeeder extends Seeder
{
    public function run(): void
    {
        // 1. Short Stay: Executive Penthouse in Bole Atlas
        $prop1 = Property::updateOrCreate(
            ['property_code' => 'PROP-STAY-001'],
            [
                'title' => 'Executive Penthouse with Skyline Views in Bole',
                'description' => "Panoramic penthouse overlooking Bole. Features ultra-fast fiber WiFi, daily housekeeping, 24/7 power backup generator, and dedicated airport transfer service.",
                'building_area' => 180,
                'bedrooms' => 2,
                'bathrooms' => 2,
                'construction_year' => 2024,
                'completion_pct' => 100,
                'status' => 'available',
                'latitude' => 8.9950,
                'longitude' => 38.7890,
            ]
        );

        $prop1->media()->firstOrCreate(
            ['storage_key' => 'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?auto=format&fit=crop&w=1200&q=80'],
            [
                'kind' => 'image',
                'mime_type' => 'image/jpeg',
                'width' => 1920,
                'height' => 1080,
                'sort_order' => 0,
                'visibility' => 'public',
                'status' => 'processed',
            ]
        );

        Listing::updateOrCreate(
            ['property_id' => $prop1->id, 'purpose' => 'Short Stay'],
            [
                'price' => 6500,
                'currency' => 'ETB',
                'status' => 'published',
                'visibility' => 'public',
                'published_at' => now(),
            ]
        );

        // 2. Land: Prime Commercial Plot in Ayat / Legetafo
        $prop2 = Property::updateOrCreate(
            ['property_code' => 'PROP-LAND-002'],
            [
                'title' => 'Prime 1,000 m² Commercial Development Plot',
                'description' => "Corner commercial plot with dual asphalt access, clear ownership title certificate (Liyu Mesriya), and immediate construction clearance. Ideal for mixed-use retail and residential apartments.",
                'land_area' => 1000,
                'building_area' => 0,
                'bedrooms' => 0,
                'bathrooms' => 0,
                'status' => 'available',
                'latitude' => 9.0420,
                'longitude' => 38.8650,
            ]
        );

        $prop2->media()->firstOrCreate(
            ['storage_key' => 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?auto=format&fit=crop&w=1200&q=80'],
            [
                'kind' => 'image',
                'mime_type' => 'image/jpeg',
                'width' => 1920,
                'height' => 1080,
                'sort_order' => 0,
                'visibility' => 'public',
                'status' => 'processed',
            ]
        );

        Listing::updateOrCreate(
            ['property_id' => $prop2->id, 'purpose' => 'Land'],
            [
                'price' => 38000000,
                'currency' => 'ETB',
                'status' => 'published',
                'visibility' => 'public',
                'published_at' => now(),
            ]
        );

        // 3. New Development: The Skyrise Residences off-plan in Bole Medhanialem
        $prop3 = Property::updateOrCreate(
            ['property_code' => 'PROP-DEV-003'],
            [
                'title' => 'The Skyrise Luxury Residences (Off-Plan)',
                'description' => "Pre-launch pricing for Addis Ababa's premier 24-storey residential tower. Features rooftop infinity pool, gym, underground smart parking, bank financing partnership, and 3-year phased payment schedule.",
                'building_area' => 165,
                'bedrooms' => 3,
                'bathrooms' => 3,
                'construction_year' => 2026,
                'completion_pct' => 45,
                'status' => 'available',
                'latitude' => 9.0080,
                'longitude' => 38.7910,
            ]
        );

        $prop3->media()->firstOrCreate(
            ['storage_key' => 'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?auto=format&fit=crop&w=1200&q=80'],
            [
                'kind' => 'image',
                'mime_type' => 'image/jpeg',
                'width' => 1920,
                'height' => 1080,
                'sort_order' => 0,
                'visibility' => 'public',
                'status' => 'processed',
            ]
        );

        Listing::updateOrCreate(
            ['property_id' => $prop3->id, 'purpose' => 'Development'],
            [
                'price' => 24500000,
                'currency' => 'ETB',
                'status' => 'published',
                'visibility' => 'public',
                'published_at' => now(),
            ]
        );
    }
}
