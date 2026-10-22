<?php

namespace App\Domains\Location\Http\Controllers\Api\V1;

use App\Domains\Location\Http\Controllers\Controller;
use App\Domains\Location\Models\City;
use App\Domains\Location\Models\Kebele;
use App\Domains\Location\Models\Region;
use App\Domains\Location\Models\SubCity;
use App\Domains\Location\Models\Woreda;
use App\Domains\Location\Models\Zone;
use Illuminate\Http\Request;

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
