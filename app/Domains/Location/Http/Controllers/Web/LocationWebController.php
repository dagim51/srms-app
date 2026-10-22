<?php

namespace App\Domains\Location\Http\Controllers\Web;

use App\Domains\Location\Models\City;
use App\Domains\Location\Models\Region;
use App\Domains\Location\Models\SubCity;
use App\Domains\Location\Models\Woreda;
use App\Domains\Location\Models\Zone;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LocationWebController extends Controller
{
    /**
     * Display location administrative management overview.
     */
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'subcities');
        $search = $request->query('search');
        $regionId = $request->query('region_id');
        $status = $request->query('status');

        $totalSubCities = SubCity::count();
        $totalZones = Zone::count();
        $totalWoredas = Woreda::count();
        $activeUnits = SubCity::where('status', 'active')->count()
            + Zone::where('status', 'active')->count()
            + Woreda::where('status', 'active')->count();

        $regions = Region::where('status', 'active')->with('cities')->orderBy('name_en')->get();
        $cities = City::where('status', 'active')->orderBy('name_en')->get();

        // Sub-cities query
        $subCitiesQuery = SubCity::with(['city.region', 'woredas'])->latest();
        if ($search) {
            $subCitiesQuery->where(function ($q) use ($search) {
                $q->where('name_en', 'like', "%{$search}%")
                    ->orWhere('name_am', 'like', "%{$search}%")
                    ->orWhere('name_om', 'like', "%{$search}%");
            });
        }
        if ($regionId) {
            $subCitiesQuery->whereHas('city', fn ($q) => $q->where('region_id', $regionId));
        }
        if ($status) {
            $subCitiesQuery->where('status', $status);
        }
        $subCities = $subCitiesQuery->paginate(15, ['*'], 'subcities_page')->withQueryString();

        // Zones query
        $zonesQuery = Zone::with(['region', 'woredas'])->latest();
        if ($search) {
            $zonesQuery->where(function ($q) use ($search) {
                $q->where('name_en', 'like', "%{$search}%")
                    ->orWhere('name_am', 'like', "%{$search}%")
                    ->orWhere('name_om', 'like', "%{$search}%");
            });
        }
        if ($regionId) {
            $zonesQuery->where('region_id', $regionId);
        }
        if ($status) {
            $zonesQuery->where('status', $status);
        }
        $zones = $zonesQuery->paginate(15, ['*'], 'zones_page')->withQueryString();

        // Woredas query
        $woredasQuery = Woreda::with(['subCity.city.region', 'zone.region'])->latest();
        if ($search) {
            $woredasQuery->where(function ($q) use ($search) {
                $q->where('name_en', 'like', "%{$search}%")
                    ->orWhere('name_am', 'like', "%{$search}%")
                    ->orWhere('name_om', 'like', "%{$search}%");
            });
        }
        if ($regionId) {
            $woredasQuery->where(function ($q) use ($regionId) {
                $q->whereHas('subCity.city', fn ($cq) => $cq->where('region_id', $regionId))
                    ->orWhereHas('zone', fn ($zq) => $zq->where('region_id', $regionId));
            });
        }
        if ($subCityId = $request->query('sub_city_id')) {
            $woredasQuery->where('sub_city_id', $subCityId);
        }
        if ($zoneId = $request->query('zone_id')) {
            $woredasQuery->where('zone_id', $zoneId);
        }
        if ($status) {
            $woredasQuery->where('status', $status);
        }
        $woredas = $woredasQuery->paginate(15, ['*'], 'woredas_page')->withQueryString();

        return view('dashboard.locations.index', compact(
            'tab',
            'totalSubCities',
            'totalZones',
            'totalWoredas',
            'activeUnits',
            'regions',
            'cities',
            'subCities',
            'zones',
            'woredas'
        ));
    }

    /**
     * Store a newly created Sub-City.
     */
    public function storeSubCity(Request $request)
    {
        $validated = $request->validate([
            'city_id' => 'required|string|exists:cities,id',
            'name_en' => 'required|string|max:100',
            'name_am' => 'nullable|string|max:100',
            'name_om' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'status' => 'nullable|string|in:active,inactive',
        ]);

        $subCity = SubCity::create([
            'city_id' => $validated['city_id'],
            'name_en' => $validated['name_en'],
            'name_am' => $validated['name_am'] ?? null,
            'name_om' => $validated['name_om'] ?? null,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'status' => $validated['status'] ?? 'active',
        ]);

        return redirect()->route('dashboard.locations.index', ['tab' => 'subcities'])
            ->with('success', "Sub-City '{$subCity->name_en}' created successfully.");
    }

    /**
     * Update an existing Sub-City.
     */
    public function updateSubCity(Request $request, SubCity $subCity)
    {
        $validated = $request->validate([
            'city_id' => 'required|string|exists:cities,id',
            'name_en' => 'required|string|max:100',
            'name_am' => 'nullable|string|max:100',
            'name_om' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'status' => 'nullable|string|in:active,inactive',
        ]);

        $subCity->update($validated);

        return redirect()->route('dashboard.locations.index', ['tab' => 'subcities'])
            ->with('success', "Sub-City '{$subCity->name_en}' updated successfully.");
    }

    /**
     * Store a newly created Zone.
     */
    public function storeZone(Request $request)
    {
        $validated = $request->validate([
            'region_id' => 'required|string|exists:regions,id',
            'name_en' => 'required|string|max:100',
            'name_am' => 'nullable|string|max:100',
            'name_om' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'status' => 'nullable|string|in:active,inactive',
        ]);

        $zone = Zone::create([
            'region_id' => $validated['region_id'],
            'name_en' => $validated['name_en'],
            'name_am' => $validated['name_am'] ?? null,
            'name_om' => $validated['name_om'] ?? null,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'status' => $validated['status'] ?? 'active',
        ]);

        return redirect()->route('dashboard.locations.index', ['tab' => 'zones'])
            ->with('success', "Zone '{$zone->name_en}' created successfully.");
    }

    /**
     * Update an existing Zone.
     */
    public function updateZone(Request $request, Zone $zone)
    {
        $validated = $request->validate([
            'region_id' => 'required|string|exists:regions,id',
            'name_en' => 'required|string|max:100',
            'name_am' => 'nullable|string|max:100',
            'name_om' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'status' => 'nullable|string|in:active,inactive',
        ]);

        $zone->update($validated);

        return redirect()->route('dashboard.locations.index', ['tab' => 'zones'])
            ->with('success', "Zone '{$zone->name_en}' updated successfully.");
    }

    /**
     * Store a newly created Woreda.
     */
    public function storeWoreda(Request $request)
    {
        $validated = $request->validate([
            'parent_type' => 'required|string|in:sub_city,zone',
            'sub_city_id' => 'nullable|required_if:parent_type,sub_city|exists:sub_cities,id',
            'zone_id' => 'nullable|required_if:parent_type,zone|exists:zones,id',
            'name_en' => 'required|string|max:100',
            'name_am' => 'nullable|string|max:100',
            'name_om' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'status' => 'nullable|string|in:active,inactive',
        ]);

        $woreda = Woreda::create([
            'sub_city_id' => $validated['parent_type'] === 'sub_city' ? $validated['sub_city_id'] : null,
            'zone_id' => $validated['parent_type'] === 'zone' ? $validated['zone_id'] : null,
            'name_en' => $validated['name_en'],
            'name_am' => $validated['name_am'] ?? null,
            'name_om' => $validated['name_om'] ?? null,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'status' => $validated['status'] ?? 'active',
        ]);

        return redirect()->route('dashboard.locations.index', ['tab' => 'woredas'])
            ->with('success', "Woreda '{$woreda->name_en}' created successfully.");
    }

    /**
     * Update an existing Woreda.
     */
    public function updateWoreda(Request $request, Woreda $woreda)
    {
        $validated = $request->validate([
            'parent_type' => 'required|string|in:sub_city,zone',
            'sub_city_id' => 'nullable|required_if:parent_type,sub_city|exists:sub_cities,id',
            'zone_id' => 'nullable|required_if:parent_type,zone|exists:zones,id',
            'name_en' => 'required|string|max:100',
            'name_am' => 'nullable|string|max:100',
            'name_om' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'status' => 'nullable|string|in:active,inactive',
        ]);

        $woreda->update([
            'sub_city_id' => $validated['parent_type'] === 'sub_city' ? $validated['sub_city_id'] : null,
            'zone_id' => $validated['parent_type'] === 'zone' ? $validated['zone_id'] : null,
            'name_en' => $validated['name_en'],
            'name_am' => $validated['name_am'] ?? null,
            'name_om' => $validated['name_om'] ?? null,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'status' => $validated['status'] ?? 'active',
        ]);

        return redirect()->route('dashboard.locations.index', ['tab' => 'woredas'])
            ->with('success', "Woreda '{$woreda->name_en}' updated successfully.");
    }

    /**
     * Toggle active/inactive status for any location type.
     */
    public function toggleStatus(string $type, string $id)
    {
        $model = match ($type) {
            'subcity' => SubCity::findOrFail($id),
            'zone' => Zone::findOrFail($id),
            'woreda' => Woreda::findOrFail($id),
            default => abort(404),
        };

        $model->status = $model->status === 'active' ? 'inactive' : 'active';
        $model->save();

        $tab = match ($type) {
            'subcity' => 'subcities',
            'zone' => 'zones',
            'woreda' => 'woredas',
        };

        return redirect()->route('dashboard.locations.index', ['tab' => $tab])
            ->with('success', "Status for '{$model->name_en}' updated to {$model->status}.");
    }

    /**
     * Soft delete a location unit.
     */
    public function destroy(string $type, string $id)
    {
        $model = match ($type) {
            'subcity' => SubCity::findOrFail($id),
            'zone' => Zone::findOrFail($id),
            'woreda' => Woreda::findOrFail($id),
            default => abort(404),
        };

        $name = $model->name_en;
        $model->delete();

        $tab = match ($type) {
            'subcity' => 'subcities',
            'zone' => 'zones',
            'woreda' => 'woredas',
        };

        return redirect()->route('dashboard.locations.index', ['tab' => $tab])
            ->with('success', "{$name} deleted successfully.");
    }

    /**
     * Return JSON hierarchy for dynamic cascade dropdowns.
     */
    public function cascadeData()
    {
        $regions = Region::where('status', 'active')
            ->select('id', 'name_en', 'name_am', 'name_om')
            ->with(['cities' => fn ($q) => $q->where('status', 'active')->select('id', 'region_id', 'name_en')])
            ->orderBy('name_en')
            ->get();

        $subCities = SubCity::where('status', 'active')
            ->select('id', 'city_id', 'name_en', 'name_am', 'name_om')
            ->with('city:id,region_id')
            ->orderBy('name_en')
            ->get()
            ->map(function (SubCity $sc) {
                return [
                    'id' => $sc->id,
                    'type' => 'sub_city',
                    'city_id' => $sc->city_id,
                    'region_id' => $sc->city?->region_id,
                    'name_en' => $sc->name_en,
                    'name_am' => $sc->name_am,
                    'name_om' => $sc->name_om,
                ];
            });

        $zones = Zone::where('status', 'active')
            ->select('id', 'region_id', 'name_en', 'name_am', 'name_om')
            ->orderBy('name_en')
            ->get()
            ->map(function (Zone $z) {
                return [
                    'id' => $z->id,
                    'type' => 'zone',
                    'region_id' => $z->region_id,
                    'name_en' => $z->name_en,
                    'name_am' => $z->name_am,
                    'name_om' => $z->name_om,
                ];
            });

        $woredas = Woreda::where('status', 'active')
            ->select('id', 'zone_id', 'sub_city_id', 'name_en', 'name_am', 'name_om')
            ->orderBy('name_en')
            ->get();

        return response()->json([
            'regions' => $regions,
            'sub_cities' => $subCities,
            'zones' => $zones,
            'woredas' => $woredas,
        ]);
    }
}
