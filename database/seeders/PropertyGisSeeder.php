<?php

namespace Database\Seeders;

use App\Models\Listing;
use App\Models\Property;
use Illuminate\Database\Seeder;

class PropertyGisSeeder extends Seeder
{
    public function run(): void
    {
        $propertiesData = [
            [
                'property_code' => 'PROP-ADD-001',
                'title' => 'Luxury Modern Villa in Bole Atlas',
                'description' => 'Spectacular 5-bedroom residence in prime Bole Atlas with imported marble, private garden, and backup generator.',
                'status' => 'available',
                'bedrooms' => 5,
                'bathrooms' => 4,
                'building_area' => 450,
                'land_area' => 600,
                'construction_year' => 2024,
                'latitude' => 9.0012,
                'longitude' => 38.7845,
                'image' => 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=800&q=80',
                'price' => 45000000,
                'purpose' => 'For Sale',
            ],
            [
                'property_code' => 'PROP-ADD-002',
                'title' => 'Diplomatic Residence in Old Airport',
                'description' => 'High-security executive villa in Old Airport / Sarbet area near international embassies and community clubs.',
                'status' => 'available',
                'bedrooms' => 4,
                'bathrooms' => 3.5,
                'building_area' => 380,
                'land_area' => 500,
                'construction_year' => 2023,
                'latitude' => 8.9950,
                'longitude' => 38.7420,
                'image' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=800&q=80',
                'price' => 38000000,
                'purpose' => 'For Sale',
            ],
            [
                'property_code' => 'PROP-ADD-003',
                'title' => 'Penthouse Apartment in Kazanchis',
                'description' => 'Ultra-modern 3-bedroom penthouse with breathtaking panoramic views of UNECA and Mount Entoto.',
                'status' => 'available',
                'bedrooms' => 3,
                'bathrooms' => 3,
                'building_area' => 240,
                'land_area' => null,
                'construction_year' => 2025,
                'latitude' => 9.0180,
                'longitude' => 38.7660,
                'image' => 'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?auto=format&fit=crop&w=800&q=80',
                'price' => 26000000,
                'purpose' => 'For Sale',
            ],
            [
                'property_code' => 'PROP-ADD-004',
                'title' => 'Family Townhouse in CMC Michael',
                'description' => 'Contemporary 4-bedroom townhouse in serene gated community with 24/7 security and children playground.',
                'status' => 'available',
                'bedrooms' => 4,
                'bathrooms' => 3,
                'building_area' => 290,
                'land_area' => 220,
                'construction_year' => 2023,
                'latitude' => 9.0230,
                'longitude' => 38.8350,
                'image' => 'https://images.unsplash.com/photo-1600566753376-12c8ab7fb75b?auto=format&fit=crop&w=800&q=80',
                'price' => 19500000,
                'purpose' => 'For Sale',
            ],
            [
                'property_code' => 'PROP-ADD-005',
                'title' => 'Furnished Apartment in Bole Rwanda',
                'description' => 'Elegantly furnished 2-bedroom executive apartment minutes from Bole International Airport.',
                'status' => 'available',
                'bedrooms' => 2,
                'bathrooms' => 2,
                'building_area' => 135,
                'land_area' => null,
                'construction_year' => 2024,
                'latitude' => 8.9890,
                'longitude' => 38.7830,
                'image' => 'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?auto=format&fit=crop&w=800&q=80',
                'price' => 120000,
                'purpose' => 'For Rent',
            ],
            [
                'property_code' => 'PROP-ADD-006',
                'title' => 'Commercial Building Floor in Piassa',
                'description' => 'Prime office space on Churchill Avenue, ideal for corporate headquarters, banks, or consultancies.',
                'status' => 'available',
                'bedrooms' => 0,
                'bathrooms' => 2,
                'building_area' => 350,
                'land_area' => null,
                'construction_year' => 2022,
                'latitude' => 9.0350,
                'longitude' => 38.7530,
                'image' => 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=800&q=80',
                'price' => 250000,
                'purpose' => 'For Rent',
            ],
            [
                'property_code' => 'PROP-ADD-007',
                'title' => 'Modern Villa with Pool in Bisrate Gabriel',
                'description' => 'Spectacular private estate featuring heated swimming pool, sauna, and lush mature garden.',
                'status' => 'available',
                'bedrooms' => 6,
                'bathrooms' => 5,
                'building_area' => 520,
                'land_area' => 750,
                'construction_year' => 2024,
                'latitude' => 8.9850,
                'longitude' => 38.7350,
                'image' => 'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?auto=format&fit=crop&w=800&q=80',
                'price' => 65000000,
                'purpose' => 'For Sale',
            ],
            [
                'property_code' => 'PROP-ADD-008',
                'title' => 'Spacious Duplex in Summit Condominiums',
                'description' => '3-bedroom duplex apartment with reserved parking, reliable water tank, and elevator access.',
                'status' => 'available',
                'bedrooms' => 3,
                'bathrooms' => 2,
                'building_area' => 165,
                'land_area' => null,
                'construction_year' => 2023,
                'latitude' => 9.0150,
                'longitude' => 38.8550,
                'image' => 'https://images.unsplash.com/photo-1600573472591-ee6981cf35b6?auto=format&fit=crop&w=800&q=80',
                'price' => 14500000,
                'purpose' => 'For Sale',
            ],
        ];

        foreach ($propertiesData as $data) {
            $image = $data['image'];
            $price = $data['price'];
            $purpose = $data['purpose'];
            unset($data['image'], $data['price'], $data['purpose']);

            $property = Property::updateOrCreate(
                ['property_code' => $data['property_code']],
                $data
            );

            // Add cover media
            $property->media()->firstOrCreate(
                ['storage_key' => $image],
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

            // Add active listing
            Listing::firstOrCreate(
                ['property_id' => $property->id],
                [
                    'purpose' => $purpose,
                    'price' => $price,
                    'currency' => 'ETB',
                    'status' => 'published',
                    'visibility' => 'public',
                    'published_at' => now(),
                ]
            );
        }
    }
}
