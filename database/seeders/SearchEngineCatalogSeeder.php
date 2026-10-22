<?php

namespace Database\Seeders;

use App\Models\Amenity;
use App\Models\Property;
use App\Models\PropertyCategory;
use App\Models\PropertyType;
use Illuminate\Database\Seeder;

class SearchEngineCatalogSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed Property Categories
        $categories = [
            ['code' => 'residential', 'name_en' => 'Residential', 'status' => 'active'],
            ['code' => 'commercial', 'name_en' => 'Commercial', 'status' => 'active'],
            ['code' => 'land', 'name_en' => 'Land & Plots', 'status' => 'active'],
            ['code' => 'industrial', 'name_en' => 'Industrial', 'status' => 'active'],
        ];

        $categoryMap = [];
        foreach ($categories as $cat) {
            $created = PropertyCategory::firstOrCreate(
                ['code' => $cat['code']],
                $cat
            );
            $categoryMap[$cat['code']] = $created->id;
        }

        // 2. Seed Property Types
        $types = [
            ['code' => 'apartment', 'name_en' => 'Apartment', 'category_code' => 'residential', 'status' => 'active'],
            ['code' => 'villa', 'name_en' => 'Villa', 'category_code' => 'residential', 'status' => 'active'],
            ['code' => 'penthouse', 'name_en' => 'Penthouse', 'category_code' => 'residential', 'status' => 'active'],
            ['code' => 'townhouse', 'name_en' => 'Townhouse', 'category_code' => 'residential', 'status' => 'active'],
            ['code' => 'office', 'name_en' => 'Office Space', 'category_code' => 'commercial', 'status' => 'active'],
            ['code' => 'commercial_building', 'name_en' => 'Commercial Building', 'category_code' => 'commercial', 'status' => 'active'],
            ['code' => 'commercial_plot', 'name_en' => 'Commercial Plot', 'category_code' => 'land', 'status' => 'active'],
            ['code' => 'residential_plot', 'name_en' => 'Residential Plot', 'category_code' => 'land', 'status' => 'active'],
        ];

        $typeMap = [];
        foreach ($types as $typeData) {
            $catId = $categoryMap[$typeData['category_code']] ?? null;
            $created = PropertyType::firstOrCreate(
                ['code' => $typeData['code']],
                [
                    'name_en' => $typeData['name_en'],
                    'category_id' => $catId,
                    'status' => 'active',
                ]
            );
            $typeMap[$typeData['code']] = $created->id;
        }

        // 3. Link Existing Properties with Types & Verification Status & Amenities
        $villaType = $typeMap['villa'] ?? null;
        $apartmentType = $typeMap['apartment'] ?? null;
        $penthouseType = $typeMap['penthouse'] ?? null;
        $landType = $typeMap['commercial_plot'] ?? null;
        $residentialCat = $categoryMap['residential'] ?? null;

        $properties = Property::all();
        $generatorAmenity = Amenity::where('code', 'backup_generator')->first();
        $waterAmenity = Amenity::where('code', 'water_reservoir_tank')->first();
        $securityAmenity = Amenity::where('code', 'security_guard_24_7')->first();
        $parkingAmenity = Amenity::where('code', 'ample_parking')->first();
        $elevatorAmenity = Amenity::where('code', 'elevator_lift')->first();

        foreach ($properties as $index => $prop) {
            // Determine type by title
            $titleLower = strtolower($prop->title);
            $chosenType = $apartmentType;
            if (str_contains($titleLower, 'villa')) {
                $chosenType = $villaType;
            } elseif (str_contains($titleLower, 'penthouse')) {
                $chosenType = $penthouseType;
            } elseif (str_contains($titleLower, 'plot') || str_contains($titleLower, 'land')) {
                $chosenType = $landType;
            }

            // Set verified status on some properties
            $status = ($index % 2 === 0) ? 'verified' : 'available';

            $prop->update([
                'type_id' => $chosenType,
                'category_id' => $residentialCat,
                'status' => $status,
            ]);

            // Attach standard amenities
            $amenityIds = [];
            if ($generatorAmenity) {
                $amenityIds[] = $generatorAmenity->id;
            }
            if ($waterAmenity) {
                $amenityIds[] = $waterAmenity->id;
            }
            if ($securityAmenity) {
                $amenityIds[] = $securityAmenity->id;
            }
            if ($parkingAmenity) {
                $amenityIds[] = $parkingAmenity->id;
            }
            if ($index % 3 === 0 && $elevatorAmenity) {
                $amenityIds[] = $elevatorAmenity->id;
            }

            $prop->amenities()->syncWithoutDetaching($amenityIds);
        }
    }
}
