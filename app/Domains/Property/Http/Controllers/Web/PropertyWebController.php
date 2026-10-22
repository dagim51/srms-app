<?php

namespace App\Domains\Property\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessPropertyMedia;
use App\Models\Amenity;
use App\Models\Neighborhood;
use App\Models\Property;
use App\Models\PropertyCategory;
use App\Models\PropertyFeature;
use App\Models\PropertyType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PropertyWebController extends Controller
{
    /**
     * Display a listing of properties.
     */
    public function index(Request $request)
    {
        $query = Property::with(['media', 'category', 'type', 'subCity', 'zone', 'woreda'])
            ->latest();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('property_code', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%")
                    ->orWhereHas('subCity', fn ($sq) => $sq->where('name_en', 'like', "%{$search}%")->orWhere('name_am', 'like', "%{$search}%")->orWhere('name_om', 'like', "%{$search}%"))
                    ->orWhereHas('zone', fn ($zq) => $zq->where('name_en', 'like', "%{$search}%")->orWhere('name_am', 'like', "%{$search}%")->orWhere('name_om', 'like', "%{$search}%"))
                    ->orWhereHas('woreda', fn ($wq) => $wq->where('name_en', 'like', "%{$search}%")->orWhere('name_am', 'like', "%{$search}%")->orWhere('name_om', 'like', "%{$search}%"));
            });
        }

        if ($typeId = $request->input('type_id')) {
            $query->where('type_id', $typeId);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $properties = $query->paginate(10)->withQueryString();
        $types = PropertyType::where('status', 'active')->orderBy('name_en')->get();

        return view('dashboard.properties.index', compact('properties', 'types'));
    }

    /**
     * Show the form for creating a new property.
     */
    public function create()
    {
        $categories = PropertyCategory::where('status', 'active')
            ->orderBy('name_en')
            ->get();

        $types = PropertyType::where('status', 'active')
            ->orderBy('name_en')
            ->get();

        $amenities = Amenity::where('status', 'active')
            ->orderBy('category')
            ->orderBy('name_en')
            ->get();

        $features = PropertyFeature::where('status', 'active')
            ->orderBy('name_en')
            ->get();

        return view('dashboard.properties.create', compact('categories', 'types', 'amenities', 'features'));
    }

    /**
     * Store a newly created property in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'nullable|string|max:100',
            'type_id' => 'nullable|string|max:36',
            'category_id' => 'nullable|string|max:36',
            'status' => 'nullable|string|in:draft,available,under_construction,verified',
            'address' => 'nullable|string|max:255',
            'location_id' => 'nullable|string|max:36',
            'region_id' => 'nullable|string|max:36',
            'zone_id' => 'nullable|string|max:36',
            'sub_city_id' => 'nullable|string|max:36',
            'woreda_id' => 'nullable|string|max:36',
            'bedrooms' => 'nullable|integer|min:0',
            'bathrooms' => 'nullable|numeric|min:0',
            'area' => 'nullable|numeric|min:0',
            'building_area' => 'nullable|numeric|min:0',
            'land_area' => 'nullable|numeric|min:0',
            'year_built' => 'nullable|integer|min:1900|max:'.(date('Y') + 5),
            'construction_year' => 'nullable|integer|min:1900|max:'.(date('Y') + 5),
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'amenities' => 'nullable|array',
            'amenities.*.id' => 'nullable|string',
            'amenities.*.value' => 'nullable|string|max:255',
            'features' => 'nullable|array',
            'features.*.id' => 'nullable|string',
            'features.*.value' => 'nullable|string|max:255',
            'photos' => 'nullable|array',
            'photos.*' => 'nullable|file|mimes:jpeg,png,jpg,webp,mp4,mov,pdf|max:20480',
        ]);

        return DB::transaction(function () use ($request, $validated) {
            // Resolve property type and category
            $typeId = $validated['type_id'] ?? null;
            $categoryId = $validated['category_id'] ?? null;

            $typeInput = $request->input('type');
            if (empty($typeId) && ! empty($typeInput)) {
                $matchedType = PropertyType::where('id', $typeInput)
                    ->orWhere('code', $typeInput)
                    ->first();

                if ($matchedType) {
                    $typeId = $matchedType->id;
                    if (empty($categoryId)) {
                        $categoryId = $matchedType->category_id;
                    }
                }
            } elseif (! empty($typeId) && empty($categoryId)) {
                $matchedType = PropertyType::find($typeId);
                if ($matchedType) {
                    $categoryId = $matchedType->category_id;
                }
            }

            // Resolve location_id: only persist if valid Neighborhood UUID exists
            $locationId = null;
            if (! empty($validated['location_id'])) {
                if (Neighborhood::where('id', $validated['location_id'])->exists()) {
                    $locationId = $validated['location_id'];
                }
            }

            // Area resolution
            $buildingArea = $validated['building_area'] ?? ($validated['area'] ?? null);
            $landArea = $validated['land_area'] ?? ($validated['area'] ?? null);

            // Construction year resolution
            $constructionYear = $validated['construction_year'] ?? ($validated['year_built'] ?? null);

            // Generate unique property code
            do {
                $propertyCode = 'PROP-'.date('Y').'-'.strtoupper(Str::random(6));
            } while (Property::where('property_code', $propertyCode)->exists());

            // Create Property
            $property = Property::create([
                'property_code' => $propertyCode,
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'category_id' => $categoryId,
                'type_id' => $typeId,
                'owner_user_id' => Auth::id(),
                'organization_id' => Auth::user()?->organization_id ?? null,
                'location_id' => $locationId,
                'region_id' => $validated['region_id'] ?? null,
                'zone_id' => $validated['zone_id'] ?? null,
                'sub_city_id' => $validated['sub_city_id'] ?? null,
                'woreda_id' => $validated['woreda_id'] ?? null,
                'address' => $validated['address'] ?? null,
                'building_area' => $buildingArea,
                'land_area' => $landArea,
                'bedrooms' => $validated['bedrooms'] ?? null,
                'bathrooms' => $validated['bathrooms'] ?? null,
                'construction_year' => $constructionYear,
                'status' => $validated['status'] ?? 'draft',
                'latitude' => $validated['latitude'] ?? null,
                'longitude' => $validated['longitude'] ?? null,
            ]);

            // Sync Amenities
            if (! empty($validated['amenities']) && is_array($validated['amenities'])) {
                $amenitySync = [];
                foreach ($validated['amenities'] as $item) {
                    if (! empty($item['id']) && Amenity::where('id', $item['id'])->exists()) {
                        $amenitySync[$item['id']] = [
                            'value' => $item['value'] ?? null,
                        ];
                    }
                }
                if (! empty($amenitySync)) {
                    $property->amenities()->sync($amenitySync);
                }
            }

            // Sync Features
            if (! empty($validated['features']) && is_array($validated['features'])) {
                $featureSync = [];
                foreach ($validated['features'] as $item) {
                    if (! empty($item['id']) && PropertyFeature::where('id', $item['id'])->exists()) {
                        $featureSync[$item['id']] = [
                            'value' => $item['value'] ?? null,
                        ];
                    }
                }
                if (! empty($featureSync)) {
                    $property->features()->sync($featureSync);
                }
            }

            // Handle Media uploads (photos)
            if ($request->hasFile('photos')) {
                $sortOrder = 0;
                foreach ($request->file('photos') as $file) {
                    if (! $file->isValid()) {
                        continue;
                    }

                    $mimeType = $file->getMimeType();
                    $kind = 'image';
                    if (str_starts_with($mimeType, 'video')) {
                        $kind = 'video';
                    } elseif ($mimeType === 'application/pdf') {
                        $kind = 'document';
                    }

                    $path = $file->store("properties/{$property->id}", 'public');

                    $width = null;
                    $height = null;
                    if ($kind === 'image') {
                        $imageInfo = @getimagesize($file->getRealPath());
                        if ($imageInfo) {
                            $width = $imageInfo[0];
                            $height = $imageInfo[1];
                        }
                    }

                    $media = $property->media()->create([
                        'kind' => $kind,
                        'storage_key' => $path,
                        'mime_type' => $mimeType,
                        'width' => $width,
                        'height' => $height,
                        'checksum' => md5_file($file->getRealPath()),
                        'status' => 'processed',
                        'visibility' => 'public',
                        'sort_order' => $sortOrder++,
                    ]);

                    if (class_exists(ProcessPropertyMedia::class)) {
                        try {
                            ProcessPropertyMedia::dispatch($media);
                        } catch (\Throwable $e) {
                            // Queue fallback gracefully
                        }
                    }
                }
            }

            return redirect()->route('dashboard.properties.index')
                ->with('success', "Property '{$property->title}' ({$property->property_code}) registered successfully!");
        });
    }

    /**
     * Show the form for editing the specified property.
     */
    public function edit(Property $property)
    {
        $property->load(['amenities', 'features', 'media']);

        $categories = PropertyCategory::where('status', 'active')
            ->orderBy('name_en')
            ->get();

        $types = PropertyType::where('status', 'active')
            ->orderBy('name_en')
            ->get();

        $amenities = Amenity::where('status', 'active')
            ->orderBy('category')
            ->orderBy('name_en')
            ->get();

        $features = PropertyFeature::where('status', 'active')
            ->orderBy('name_en')
            ->get();

        return view('dashboard.properties.edit', compact('property', 'categories', 'types', 'amenities', 'features'));
    }

    /**
     * Update the specified property in storage.
     */
    public function update(Request $request, Property $property)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type_id' => 'nullable|string|max:36',
            'category_id' => 'nullable|string|max:36',
            'status' => 'nullable|string|in:draft,available,under_construction,verified',
            'address' => 'nullable|string|max:255',
            'location_id' => 'nullable|string|max:36',
            'region_id' => 'nullable|string|max:36',
            'zone_id' => 'nullable|string|max:36',
            'sub_city_id' => 'nullable|string|max:36',
            'woreda_id' => 'nullable|string|max:36',
            'bedrooms' => 'nullable|integer|min:0',
            'bathrooms' => 'nullable|numeric|min:0',
            'area' => 'nullable|numeric|min:0',
            'building_area' => 'nullable|numeric|min:0',
            'land_area' => 'nullable|numeric|min:0',
            'construction_year' => 'nullable|integer|min:1900|max:'.(date('Y') + 5),
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'amenities' => 'nullable|array',
            'features' => 'nullable|array',
        ]);

        return DB::transaction(function () use ($request, $property, $validated) {
            $locationId = $property->location_id;
            if ($request->has('location_id')) {
                $locInput = $request->input('location_id');
                if (! empty($locInput) && Neighborhood::where('id', $locInput)->exists()) {
                    $locationId = $locInput;
                } elseif (empty($locInput)) {
                    $locationId = null;
                }
            }

            $buildingArea = $validated['building_area'] ?? ($validated['area'] ?? $property->building_area);
            $landArea = $validated['land_area'] ?? ($validated['area'] ?? $property->land_area);

            $property->update([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? $property->description,
                'type_id' => $validated['type_id'] ?? $property->type_id,
                'category_id' => $validated['category_id'] ?? $property->category_id,
                'status' => $validated['status'] ?? $property->status,
                'address' => $validated['address'] ?? $property->address,
                'location_id' => $locationId,
                'region_id' => $request->has('region_id') ? ($validated['region_id'] ?: null) : $property->region_id,
                'zone_id' => $request->has('zone_id') ? ($validated['zone_id'] ?: null) : $property->zone_id,
                'sub_city_id' => $request->has('sub_city_id') ? ($validated['sub_city_id'] ?: null) : $property->sub_city_id,
                'woreda_id' => $request->has('woreda_id') ? ($validated['woreda_id'] ?: null) : $property->woreda_id,
                'bedrooms' => $validated['bedrooms'] ?? $property->bedrooms,
                'bathrooms' => $validated['bathrooms'] ?? $property->bathrooms,
                'building_area' => $buildingArea,
                'land_area' => $landArea,
                'construction_year' => $validated['construction_year'] ?? $property->construction_year,
                'latitude' => $validated['latitude'] ?? $property->latitude,
                'longitude' => $validated['longitude'] ?? $property->longitude,
            ]);

            if ($request->has('amenities')) {
                $amenitySync = [];
                foreach ((array) $request->input('amenities') as $item) {
                    if (! empty($item['id'])) {
                        $amenitySync[$item['id']] = ['value' => $item['value'] ?? null];
                    }
                }
                $property->amenities()->sync($amenitySync);
            }

            if ($request->has('features')) {
                $featureSync = [];
                foreach ((array) $request->input('features') as $item) {
                    if (! empty($item['id'])) {
                        $featureSync[$item['id']] = ['value' => $item['value'] ?? null];
                    }
                }
                $property->features()->sync($featureSync);
            }

            return redirect()->route('dashboard.properties.index')
                ->with('success', "Property '{$property->title}' updated successfully.");
        });
    }

    /**
     * Remove the specified property from storage.
     */
    public function destroy(Property $property)
    {
        $property->delete();

        return redirect()->route('dashboard.properties.index')
            ->with('success', 'Property removed successfully.');
    }
}
