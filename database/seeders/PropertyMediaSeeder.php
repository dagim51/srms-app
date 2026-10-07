<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Property;
use App\Models\PropertyMedia;
use Illuminate\Support\Str;

class PropertyMediaSeeder extends Seeder
{
    public function run(): void
    {
        $property = Property::firstOrCreate(
            ['property_code' => 'PROP-2026-001'],
            [
                'title' => 'Luxury Modern Villa in Bole Atlas',
                'description' => 'A stunning 5-bedroom luxury residence located in prime Bole Atlas with high-end European finishes, extensive landscaped garden, and private parking.',
                'status' => 'draft',
                'bedrooms' => 5,
                'bathrooms' => 4,
                'building_area' => 450,
                'land_area' => 600,
                'construction_year' => 2024,
            ]
        );

        $sampleMedias = [
            [
                'kind' => 'image',
                'storage_key' => 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=1200&q=80',
                'mime_type' => 'image/jpeg',
                'width' => 1920,
                'height' => 1080,
                'sort_order' => 0, // Cover photo
                'visibility' => 'public',
                'status' => 'processed',
            ],
            [
                'kind' => 'image',
                'storage_key' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=1200&q=80',
                'mime_type' => 'image/jpeg',
                'width' => 1920,
                'height' => 1080,
                'sort_order' => 1,
                'visibility' => 'public',
                'status' => 'processed',
            ],
            [
                'kind' => 'image',
                'storage_key' => 'https://images.unsplash.com/photo-1600566753376-12c8ab7fb75b?auto=format&fit=crop&w=1200&q=80',
                'mime_type' => 'image/jpeg',
                'width' => 1920,
                'height' => 1080,
                'sort_order' => 2,
                'visibility' => 'public',
                'status' => 'processed',
            ],
            [
                'kind' => 'image',
                'storage_key' => 'https://images.unsplash.com/photo-1600573472591-ee6981cf35b6?auto=format&fit=crop&w=1200&q=80',
                'mime_type' => 'image/jpeg',
                'width' => 1920,
                'height' => 1080,
                'sort_order' => 3,
                'visibility' => 'public',
                'status' => 'processed',
            ],
        ];

        foreach ($sampleMedias as $item) {
            $property->media()->firstOrCreate(
                ['storage_key' => $item['storage_key']],
                $item
            );
        }
    }
}
