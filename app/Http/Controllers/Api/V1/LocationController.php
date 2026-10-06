<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Region;
use App\Models\Zone;
use App\Models\City;
use App\Models\SubCity;
use App\Models\Woreda;
use App\Models\Kebele;
use App\Models\Neighborhood;

class LocationController extends Controller
{
    public function regions()
    {
        return response()->json(Region::where('status', 'active')->get());
    }

    public function zones(Request $request)
    {
        $query = Zone::where('status', 'active');
        if ($request->has('region_id')) {
            $query->where('region_id', $request->region_id);
        }
        return response()->json($query->get());
    }

    public function cities(Request $request)
    {
        $query = City::where('status', 'active');
        if ($request->has('region_id')) {
            $query->where('region_id', $request->region_id);
        }
        return response()->json($query->get());
    }

    public function subCities(Request $request)
    {
        $query = SubCity::where('status', 'active');
        if ($request->has('city_id')) {
            $query->where('city_id', $request->city_id);
        }
        return response()->json($query->get());
    }

    public function woredas(Request $request)
    {
        $query = Woreda::where('status', 'active');
        if ($request->has('zone_id')) {
            $query->where('zone_id', $request->zone_id);
        }
        if ($request->has('sub_city_id')) {
            $query->where('sub_city_id', $request->sub_city_id);
        }
        return response()->json($query->get());
    }

    public function kebeles(Request $request)
    {
        $query = Kebele::where('status', 'active');
        if ($request->has('woreda_id')) {
            $query->where('woreda_id', $request->woreda_id);
        }
        return response()->json($query->get());
    }

    // Example of admin creation (should be guarded by middleware in routes)
    public function storeRegion(Request $request)
    {
        $validated = $request->validate([
            'country_id' => 'required|exists:countries,id',
            'name_en' => 'required|string',
            'name_am' => 'nullable|string',
            'name_om' => 'nullable|string',
        ]);
        $region = Region::create($validated);
        return response()->json($region, 201);
    }
}
