<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Country;
use App\Models\Kebele;
use App\Models\Neighborhood;
use App\Models\Region;
use App\Models\SubCity;
use App\Models\Woreda;
use App\Models\Zone;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        // Ethiopia
        $ethiopia = Country::firstOrCreate(
            ['code' => 'ET'],
            [
                'name_en' => 'Ethiopia',
                'name_am' => 'ኢትዮጵያ',
                'name_om' => 'Itoophiyaa',
                'latitude' => 9.1450,
                'longitude' => 40.4897,
                'status' => 'active',
            ]
        );

        // Addis Ababa Region (special region)
        $addisRegion = Region::firstOrCreate(
            ['country_id' => $ethiopia->id, 'name_en' => 'Addis Ababa'],
            [
                'name_am' => 'አዲስ አበባ',
                'name_om' => 'Finfinne',
                'latitude' => 9.03,
                'longitude' => 38.74,
                'status' => 'active',
            ]
        );

        // Oromia Region
        $oromiaRegion = Region::firstOrCreate(
            ['country_id' => $ethiopia->id, 'name_en' => 'Oromia'],
            [
                'name_am' => 'ኦሮሚያ',
                'name_om' => 'Oromiyaa',
                'latitude' => 8.5,
                'longitude' => 39.5,
                'status' => 'active',
            ]
        );

        // Amhara Region
        $amharaRegion = Region::firstOrCreate(
            ['country_id' => $ethiopia->id, 'name_en' => 'Amhara'],
            [
                'name_am' => 'አማራ',
                'name_om' => 'Amara',
                'latitude' => 11.5,
                'longitude' => 38.5,
                'status' => 'active',
            ]
        );

        // Southern Nations Region
        $snnpRegion = Region::firstOrCreate(
            ['country_id' => $ethiopia->id, 'name_en' => 'Southern Nations'],
            [
                'name_am' => 'የዲህ ብሔሮች ክልል',
                'name_om' => 'SNNPR',
                'latitude' => 6.5,
                'longitude' => 37.5,
                'status' => 'active',
            ]
        );

        // Dire Dawa
        $direDawaRegion = Region::firstOrCreate(
            ['country_id' => $ethiopia->id, 'name_en' => 'Dire Dawa'],
            [
                'name_am' => 'ድሬዳዋ',
                'name_om' => 'Dirre Dhawaa',
                'latitude' => 9.6,
                'longitude' => 41.85,
                'status' => 'active',
            ]
        );

        // Addis Ababa City (within Addis Ababa Region)
        $addisCity = City::firstOrCreate(
            ['region_id' => $addisRegion->id, 'name_en' => 'Addis Ababa'],
            [
                'name_am' => 'አዲስ አበባ',
                'name_om' => 'Finfinne',
                'latitude' => 9.03,
                'longitude' => 38.74,
                'status' => 'active',
            ]
        );

        // Other major cities
        $adirCity = City::firstOrCreate(
            ['region_id' => $amharaRegion->id, 'name_en' => 'Bahir Dar'],
            [
                'name_am' => 'ባህር ዳር',
                'name_om' => 'Baahir Dar',
                'latitude' => 11.6,
                'longitude' => 37.38,
                'status' => 'active',
            ]
        );

        $hawassaCity = City::firstOrCreate(
            ['region_id' => $snnpRegion->id, 'name_en' => 'Hawassa'],
            [
                'name_am' => 'አዋሳ',
                'name_om' => 'Awaasaa',
                'latitude' => 7.05,
                'longitude' => 38.47,
                'status' => 'active',
            ]
        );

        $mekelleCity = City::firstOrCreate(
            ['region_id' => $amharaRegion->id, 'name_en' => 'Mekelle'],
            [
                'name_am' => 'መቀሌ',
                'name_om' => 'Maqallee',
                'latitude' => 13.5,
                'longitude' => 39.47,
                'status' => 'active',
            ]
        );

        // Addis Ababa Sub-cities (11 sub-cities)
        $subCitiesData = [
            ['name_en' => 'Bole', 'name_am' => 'ቦሌ', 'name_om' => 'Boolee', 'lat' => 8.99, 'lng' => 38.79],
            ['name_en' => 'Yeka', 'name_am' => 'የካ', 'name_om' => 'Yekkaa', 'lat' => 9.03, 'lng' => 38.81],
            ['name_en' => 'Kirkos', 'name_am' => 'ቂርቆስ', 'name_om' => 'Qirqoos', 'lat' => 9.00, 'lng' => 38.77],
            ['name_en' => 'Arada', 'name_am' => 'አራዳ', 'name_om' => 'Aradaa', 'lat' => 9.03, 'lng' => 38.76],
            ['name_en' => 'Addis Ketema', 'name_am' => 'አዲስ ከተማ', 'name_om' => 'Addis Keteemaa', 'lat' => 9.02, 'lng' => 38.74],
            ['name_en' => 'Lideta', 'name_am' => 'ሊደታ', 'name_om' => 'Liiddaataa', 'lat' => 9.01, 'lng' => 38.75],
            ['name_en' => 'Nifas Silk-Lafto', 'name_am' => 'ኒፋስ ሲልክ-ላፍቶ', 'name_om' => 'Niifas Silk-Laftoo', 'lat' => 8.97, 'lng' => 38.74],
            ['name_en' => 'Kolfe Keranio', 'name_am' => 'ቆልፌ ቀራኒዮ', 'name_om' => 'Qolfee Qaraaniyoo', 'lat' => 9.01, 'lng' => 38.70],
            ['name_en' => 'Gullele', 'name_am' => 'ጉለሌ', 'name_om' => 'Gullelee', 'lat' => 9.05, 'lng' => 38.75],
            ['name_en' => 'Akaky Kaliti', 'name_am' => 'አቃቂ ቃሊቲ', 'name_om' => 'Aqaqii Qaaliti', 'lat' => 8.93, 'lng' => 38.76],
            ['name_en' => 'Lemi Kura', 'name_am' => 'ሌሚ ኩራ', 'name_om' => 'Leemii Kuuraa', 'lat' => 8.96, 'lng' => 38.80],
        ];

        foreach ($subCitiesData as $data) {
            SubCity::firstOrCreate(
                ['city_id' => $addisCity->id, 'name_en' => $data['name_en']],
                [
                    'name_am' => $data['name_am'],
                    'name_om' => $data['name_om'],
                    'latitude' => $data['lat'],
                    'longitude' => $data['lng'],
                    'status' => 'active',
                ]
            );
        }

        // Zones for Oromia (around Addis)
        $zonesData = [
            ['region_id' => $oromiaRegion->id, 'name_en' => 'East Shewa', 'name_am' => 'ምትሩ ሳዋ', 'name_om' => 'Saba Boruu', 'lat' => 8.5, 'lng' => 39.0],
            ['region_id' => $oromiaRegion->id, 'name_en' => 'West Shewa', 'name_am' => 'ምዕራብ ሳዋ', 'name_om' => 'Saba Lixaa', 'lat' => 8.9, 'lng' => 38.0],
            ['region_id' => $oromiaRegion->id, 'name_en' => 'Southwest Shewa', 'name_am' => 'ደቡብ ምዕራብ ሳዋ', 'name_om' => 'Saba Kibba Lixaa', 'lat' => 8.3, 'lng' => 38.3],
            ['region_id' => $oromiaRegion->id, 'name_en' => 'North Shewa', 'name_am' => 'ሰሜን ሳዋ', 'name_om' => 'Saba Gahaa', 'lat' => 9.5, 'lng' => 39.3],
            ['region_id' => $oromiaRegion->id, 'name_en' => 'Arsi', 'name_am' => 'አርሲ', 'name_om' => 'Arsii', 'lat' => 7.8, 'lng' => 39.5],
            ['region_id' => $oromiaRegion->id, 'name_en' => 'Bale', 'name_am' => 'ባሌ', 'name_om' => 'Baalee', 'lat' => 6.5, 'lng' => 40.0],
        ];

        foreach ($zonesData as $data) {
            Zone::firstOrCreate(
                ['region_id' => $data['region_id'], 'name_en' => $data['name_en']],
                [
                    'name_am' => $data['name_am'],
                    'name_om' => $data['name_om'],
                    'latitude' => $data['lat'],
                    'longitude' => $data['lng'],
                    'status' => 'active',
                ]
            );
        }

        // Create Woredas and Kebeles for key areas
        // Bole SubCity - key woredas
        $boleSubCity = SubCity::where('city_id', $addisCity->id)->where('name_en', 'Bole')->first();
        $yekaSubCity = SubCity::where('city_id', $addisCity->id)->where('name_en', 'Yeka')->first();
        $kirkosSubCity = SubCity::where('city_id', $addisCity->id)->where('name_en', 'Kirkos')->first();

        // East Shewa Zone
        $eastShewaZone = Zone::where('region_id', $oromiaRegion->id)->where('name_en', 'East Shewa')->first();

        // Bole SubCity Woredas
        $boleWoredas = [
            ['name_en' => 'Bole Medhanialem', 'name_am' => 'ቦሌ መድሃኒአለም', 'name_om' => 'Boolee Medhaaniyaleem', 'lat' => 9.00, 'lng' => 38.80],
            ['name_en' => 'Bole Atlas', 'name_am' => 'ቦሌ አትላስ', 'name_om' => 'Boolee Atlas', 'lat' => 8.99, 'lng' => 38.79],
            ['name_en' => 'Bole Bulbula', 'name_am' => 'ቦሌ ቡልቡላ', 'name_om' => 'Boolee Bulbulaa', 'lat' => 8.97, 'lng' => 38.78],
            ['name_en' => 'Bole Gerji', 'name_am' => 'ቦሌ ገርጂ', 'name_om' => 'Boolee Gerjii', 'lat' => 8.98, 'lng' => 38.81],
        ];

        foreach ($boleWoredas as $data) {
            $woreda = Woreda::firstOrCreate(
                ['sub_city_id' => $boleSubCity->id, 'name_en' => $data['name_en']],
                [
                    'name_am' => $data['name_am'],
                    'name_om' => $data['name_om'],
                    'latitude' => $data['lat'],
                    'longitude' => $data['lng'],
                    'status' => 'active',
                ]
            );

            // Kebeles for each woreda
            $kebeles = [
                ['name_en' => $data['name_en'].' 01', 'name_am' => $data['name_am'].' 01', 'name_om' => $data['name_om'].' 01', 'lat' => $data['lat'] + 0.001, 'lng' => $data['lng'] + 0.001],
                ['name_en' => $data['name_en'].' 02', 'name_am' => $data['name_am'].' 02', 'name_om' => $data['name_om'].' 02', 'lat' => $data['lat'] - 0.001, 'lng' => $data['lng'] + 0.001],
                ['name_en' => $data['name_en'].' 03', 'name_am' => $data['name_am'].' 03', 'name_om' => $data['name_om'].' 03', 'lat' => $data['lat'] + 0.001, 'lng' => $data['lng'] - 0.001],
            ];

            foreach ($kebeles as $k) {
                Kebele::firstOrCreate(
                    ['woreda_id' => $woreda->id, 'name_en' => $k['name_en']],
                    [
                        'name_am' => $k['name_am'],
                        'name_om' => $k['name_om'],
                        'latitude' => $k['lat'],
                        'longitude' => $k['lng'],
                        'status' => 'active',
                    ]
                );
            }
        }

        // Yeka SubCity Woredas
        $yekaWoredas = [
            ['name_en' => 'Yeka Abado', 'name_am' => 'የካ አባዶ', 'name_om' => 'Yekaa Abaddoo', 'lat' => 9.04, 'lng' => 38.82],
            ['name_en' => 'Yeka Kotebe', 'name_am' => 'የካ ቆጠበ', 'name_om' => 'Yekaa Qotabbee', 'lat' => 9.03, 'lng' => 38.83],
            ['name_en' => 'Yeka Lemi Kura', 'name_am' => 'የካ ሌሚ ኩራ', 'name_om' => 'Yekaa Leemii Kuuraa', 'lat' => 9.02, 'lng' => 38.81],
        ];

        foreach ($yekaWoredas as $data) {
            $woreda = Woreda::firstOrCreate(
                ['sub_city_id' => $yekaSubCity->id, 'name_en' => $data['name_en']],
                [
                    'name_am' => $data['name_am'],
                    'name_om' => $data['name_om'],
                    'latitude' => $data['lat'],
                    'longitude' => $data['lng'],
                    'status' => 'active',
                ]
            );

            $kebeles = [
                ['name_en' => $data['name_en'].' 01', 'name_am' => $data['name_am'].' 01', 'name_om' => $data['name_om'].' 01', 'lat' => $data['lat'] + 0.001, 'lng' => $data['lng'] + 0.001],
                ['name_en' => $data['name_en'].' 02', 'name_am' => $data['name_am'].' 02', 'name_om' => $data['name_om'].' 02', 'lat' => $data['lat'] - 0.001, 'lng' => $data['lng'] + 0.001],
            ];

            foreach ($kebeles as $k) {
                Kebele::firstOrCreate(
                    ['woreda_id' => $woreda->id, 'name_en' => $k['name_en']],
                    [
                        'name_am' => $k['name_am'],
                        'name_om' => $k['name_om'],
                        'latitude' => $k['lat'],
                        'longitude' => $k['lng'],
                        'status' => 'active',
                    ]
                );
            }
        }

        // Kirkos SubCity Woredas
        $kirkosWoredas = [
            ['name_en' => 'Kirkos', 'name_am' => 'ቂርቆስ', 'name_om' => 'Qirqoos', 'lat' => 9.00, 'lng' => 38.77],
            ['name_en' => 'Kazanchis', 'name_am' => 'ካዛንቺስ', 'name_om' => 'Qazaancis', 'lat' => 9.01, 'lng' => 38.77],
        ];

        foreach ($kirkosWoredas as $data) {
            $woreda = Woreda::firstOrCreate(
                ['sub_city_id' => $kirkosSubCity->id, 'name_en' => $data['name_en']],
                [
                    'name_am' => $data['name_am'],
                    'name_om' => $data['name_om'],
                    'latitude' => $data['lat'],
                    'longitude' => $data['lng'],
                    'status' => 'active',
                ]
            );

            $kebeles = [
                ['name_en' => $data['name_en'].' 01', 'name_am' => $data['name_am'].' 01', 'name_om' => $data['name_om'].' 01', 'lat' => $data['lat'] + 0.001, 'lng' => $data['lng'] + 0.001],
                ['name_en' => $data['name_en'].' 02', 'name_am' => $data['name_am'].' 02', 'name_om' => $data['name_om'].' 02', 'lat' => $data['lat'] - 0.001, 'lng' => $data['lng'] + 0.001],
            ];

            foreach ($kebeles as $k) {
                Kebele::firstOrCreate(
                    ['woreda_id' => $woreda->id, 'name_en' => $k['name_en']],
                    [
                        'name_am' => $k['name_am'],
                        'name_om' => $k['name_om'],
                        'latitude' => $k['lat'],
                        'longitude' => $k['lng'],
                        'status' => 'active',
                    ]
                );
            }
        }

        // East Shewa Woredas (for areas around Addis)
        $eastShewaWoredas = [
            ['name_en' => 'Ada\'a', 'name_am' => 'አዳ', 'name_om' => 'Adaa', 'lat' => 8.75, 'lng' => 38.95],
            ['name_en' => 'Lome', 'name_am' => 'ሎሜ', 'name_om' => 'Loomee', 'lat' => 8.65, 'lng' => 39.05],
        ];

        foreach ($eastShewaWoredas as $data) {
            $woreda = Woreda::firstOrCreate(
                ['zone_id' => $eastShewaZone->id, 'name_en' => $data['name_en']],
                [
                    'name_am' => $data['name_am'],
                    'name_om' => $data['name_om'],
                    'latitude' => $data['lat'],
                    'longitude' => $data['lng'],
                    'status' => 'active',
                ]
            );

            $kebeles = [
                ['name_en' => $data['name_en'].' Town', 'name_am' => $data['name_am'].' ከተማ', 'name_om' => $data['name_om'].' Magaalaa', 'lat' => $data['lat'] + 0.001, 'lng' => $data['lng'] + 0.001],
                ['name_en' => $data['name_en'].' Rural', 'name_am' => $data['name_am'].' ህብረት', 'name_om' => $data['name_om'].' Hiboopphee', 'lat' => $data['lat'] - 0.001, 'lng' => $data['lng'] - 0.001],
            ];

            foreach ($kebeles as $k) {
                Kebele::firstOrCreate(
                    ['woreda_id' => $woreda->id, 'name_en' => $k['name_en']],
                    [
                        'name_am' => $k['name_am'],
                        'name_om' => $k['name_om'],
                        'latitude' => $k['lat'],
                        'longitude' => $k['lng'],
                        'status' => 'active',
                    ]
                );
            }
        }

        // Create Neighborhoods for key kebeles (for property location linking)
        $boleMedhanialemWoreda = Woreda::where('sub_city_id', $boleSubCity->id)->where('name_en', 'Bole Medhanialem')->first();
        $boleAtlasWoreda = Woreda::where('sub_city_id', $boleSubCity->id)->where('name_en', 'Bole Atlas')->first();
        $yekaAbadoWoreda = Woreda::where('sub_city_id', $yekaSubCity->id)->where('name_en', 'Yeka Abado')->first();
        $kazanchisWoreda = Woreda::where('sub_city_id', $kirkosSubCity->id)->where('name_en', 'Kazanchis')->first();

        if ($boleMedhanialemWoreda) {
            $kebeles = Kebele::where('woreda_id', $boleMedhanialemWoreda->id)->get();
            foreach ($kebeles as $kebele) {
                Neighborhood::firstOrCreate(
                    ['kebele_id' => $kebele->id, 'name_en' => $kebele->name_en.' Area'],
                    [
                        'name_am' => $kebele->name_am.' ክፍለ ከተማ',
                        'name_om' => $kebele->name_om.' Bakka Magaalaa',
                        'latitude' => $kebele->latitude + 0.0005,
                        'longitude' => $kebele->longitude + 0.0005,
                        'status' => 'active',
                    ]
                );
            }
        }

        if ($boleAtlasWoreda) {
            $kebeles = Kebele::where('woreda_id', $boleAtlasWoreda->id)->get();
            foreach ($kebeles as $kebele) {
                Neighborhood::firstOrCreate(
                    ['kebele_id' => $kebele->id, 'name_en' => $kebele->name_en.' Area'],
                    [
                        'name_am' => $kebele->name_am.' ክፍለ ከተማ',
                        'name_om' => $kebele->name_om.' Bakka Magaalaa',
                        'latitude' => $kebele->latitude + 0.0005,
                        'longitude' => $kebele->longitude + 0.0005,
                        'status' => 'active',
                    ]
                );
            }
        }

        if ($yekaAbadoWoreda) {
            $kebeles = Kebele::where('woreda_id', $yekaAbadoWoreda->id)->get();
            foreach ($kebeles as $kebele) {
                Neighborhood::firstOrCreate(
                    ['kebele_id' => $kebele->id, 'name_en' => $kebele->name_en.' Area'],
                    [
                        'name_am' => $kebele->name_am.' ክፍለ ከተማ',
                        'name_om' => $kebele->name_om.' Bakka Magaalaa',
                        'latitude' => $kebele->latitude + 0.0005,
                        'longitude' => $kebele->longitude + 0.0005,
                        'status' => 'active',
                    ]
                );
            }
        }

        if ($kazanchisWoreda) {
            $kebeles = Kebele::where('woreda_id', $kazanchisWoreda->id)->get();
            foreach ($kebeles as $kebele) {
                Neighborhood::firstOrCreate(
                    ['kebele_id' => $kebele->id, 'name_en' => $kebele->name_en.' Area'],
                    [
                        'name_am' => $kebele->name_am.' ክፍለ ከተማ',
                        'name_om' => $kebele->name_om.' Bakka Magaalaa',
                        'latitude' => $kebele->latitude + 0.0005,
                        'longitude' => $kebele->longitude + 0.0005,
                        'status' => 'active',
                    ]
                );
            }
        }

        $this->command?->info('Location hierarchy seeded: Country, Regions, Cities, SubCities, Zones, Woredas, Kebeles, Neighborhoods');
    }
}
