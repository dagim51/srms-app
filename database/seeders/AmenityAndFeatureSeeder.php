<?php

namespace Database\Seeders;

use App\Models\Amenity;
use App\Models\PropertyFeature;
use Illuminate\Database\Seeder;

class AmenityAndFeatureSeeder extends Seeder
{
    public function run(): void
    {
        $amenities = [
            // Utilities
            ['category' => 'Utilities', 'code' => 'backup_generator', 'name_en' => 'Backup Generator', 'status' => 'active'],
            ['category' => 'Utilities', 'code' => 'water_reservoir_tank', 'name_en' => 'Water Reservoir Tank', 'status' => 'active'],
            ['category' => 'Utilities', 'code' => 'borehole_well', 'name_en' => 'Borehole / Deep Well', 'status' => 'active'],
            ['category' => 'Utilities', 'code' => 'solar_power_system', 'name_en' => 'Solar Power System', 'status' => 'active'],
            ['category' => 'Utilities', 'code' => 'three_phase_power', 'name_en' => '3-Phase Electric Power', 'status' => 'active'],
            ['category' => 'Utilities', 'code' => 'fiber_optic_internet', 'name_en' => 'Fiber Optic Internet', 'status' => 'active'],

            // Security
            ['category' => 'Security', 'code' => 'security_guard_24_7', 'name_en' => '24/7 Security Guard', 'status' => 'active'],
            ['category' => 'Security', 'code' => 'cctv_surveillance', 'name_en' => 'CCTV Surveillance', 'status' => 'active'],
            ['category' => 'Security', 'code' => 'gated_compound', 'name_en' => 'Gated Compound', 'status' => 'active'],
            ['category' => 'Security', 'code' => 'electric_fence', 'name_en' => 'Electric Perimeter Fence', 'status' => 'active'],
            ['category' => 'Security', 'code' => 'intercom_system', 'name_en' => 'Intercom System', 'status' => 'active'],

            // Indoor
            ['category' => 'Indoor', 'code' => 'modern_fitted_kitchen', 'name_en' => 'Modern Fitted Kitchen', 'status' => 'active'],
            ['category' => 'Indoor', 'code' => 'built_in_wardrobes', 'name_en' => 'Built-in Wardrobes', 'status' => 'active'],
            ['category' => 'Indoor', 'code' => 'maids_quarters', 'name_en' => "Maid's Room / Quarters", 'status' => 'active'],
            ['category' => 'Indoor', 'code' => 'laundry_room', 'name_en' => 'Dedicated Laundry Room', 'status' => 'active'],
            ['category' => 'Indoor', 'code' => 'en_suite_bathrooms', 'name_en' => 'En-suite Bathrooms', 'status' => 'active'],
            ['category' => 'Indoor', 'code' => 'storage_room', 'name_en' => 'Storage / Pantry Room', 'status' => 'active'],

            // Outdoor
            ['category' => 'Outdoor', 'code' => 'private_balcony_terrace', 'name_en' => 'Private Balcony / Terrace', 'status' => 'active'],
            ['category' => 'Outdoor', 'code' => 'private_garden', 'name_en' => 'Landscaped Garden', 'status' => 'active'],
            ['category' => 'Outdoor', 'code' => 'rooftop_terrace', 'name_en' => 'Rooftop Terrace', 'status' => 'active'],
            ['category' => 'Outdoor', 'code' => 'ample_parking', 'name_en' => 'Reserved Parking Space', 'status' => 'active'],
            ['category' => 'Outdoor', 'code' => 'children_playground', 'name_en' => "Children's Playground", 'status' => 'active'],

            // Wellness & Leisure
            ['category' => 'Wellness & Leisure', 'code' => 'swimming_pool', 'name_en' => 'Swimming Pool', 'status' => 'active'],
            ['category' => 'Wellness & Leisure', 'code' => 'gym_fitness_center', 'name_en' => 'Gym / Fitness Center', 'status' => 'active'],
            ['category' => 'Wellness & Leisure', 'code' => 'sauna_steam_bath', 'name_en' => 'Sauna & Steam Bath', 'status' => 'active'],
            ['category' => 'Wellness & Leisure', 'code' => 'clubhouse', 'name_en' => 'Clubhouse / Lounge', 'status' => 'active'],

            // Accessibility
            ['category' => 'Accessibility', 'code' => 'elevator_lift', 'name_en' => 'High-Speed Elevator / Lift', 'status' => 'active'],
            ['category' => 'Accessibility', 'code' => 'wheelchair_accessible', 'name_en' => 'Wheelchair Accessible', 'status' => 'active'],
        ];

        foreach ($amenities as $item) {
            Amenity::firstOrCreate(
                ['code' => $item['code']],
                $item
            );
        }

        $features = [
            ['code' => 'marble_flooring', 'name_en' => 'Imported Italian Marble Flooring', 'status' => 'active'],
            ['code' => 'hardwood_parquet', 'name_en' => 'Solid Hardwood Parquet', 'status' => 'active'],
            ['code' => 'gypsum_ceiling', 'name_en' => 'Designer Gypsum Ceiling with LED Cove', 'status' => 'active'],
            ['code' => 'granite_countertops', 'name_en' => 'Granite / Quartz Countertops', 'status' => 'active'],
            ['code' => 'double_glazed_windows', 'name_en' => 'Soundproof Double-Glazed Windows', 'status' => 'active'],
            ['code' => 'smart_home_automation', 'name_en' => 'Smart Home Lighting & Climate Automation', 'status' => 'active'],
            ['code' => 'fire_sprinkler_system', 'name_en' => 'Automated Fire Sprinkler & Smoke Detectors', 'status' => 'active'],
            ['code' => 'solar_water_heater', 'name_en' => 'High-Capacity Solar Water Heating', 'status' => 'active'],
            ['code' => 'high_ceilings', 'name_en' => 'High Volume Ceilings (3.2m+)', 'status' => 'active'],
            ['code' => 'walk_in_closet', 'name_en' => 'Custom Built Walk-in Closet', 'status' => 'active'],
            ['code' => 'jacuzzi_tub', 'name_en' => 'Hydro-Massage Jacuzzi Bathtub', 'status' => 'active'],
            ['code' => 'aluminum_profiles', 'name_en' => 'Heavy Duty Powder-Coated Aluminum Profiles', 'status' => 'active'],
        ];

        foreach ($features as $item) {
            PropertyFeature::firstOrCreate(
                ['code' => $item['code']],
                $item
            );
        }
    }
}
