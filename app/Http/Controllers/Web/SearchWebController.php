<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Amenity;
use App\Models\Listing;
use App\Models\PropertyCategory;
use App\Models\PropertyType;
use Illuminate\Http\Request;

class SearchWebController extends Controller
{
    /**
     * Advanced Search Engine Page
     */
    public function index(Request $request)
    {
        // 1. Base Query: Only published & public listings
        $query = Listing::with([
            'property.media' => fn($q) => $q->where('visibility', 'public')->orderBy('sort_order'),
            'property.category',
            'property.type',
            'property.amenities',
        ])
        ->where('status', 'published')
        ->where('visibility', 'public');

        // 2. Keyword / Location Filter
        $q = trim((string)($request->get('q') ?: $request->get('search') ?: $request->get('location')));
        if (!empty($q)) {
            $likeOp = \Illuminate\Support\Facades\DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
            $query->where(function ($sub) use ($q, $likeOp) {
                $sub->whereHas('property', function ($propQ) use ($q, $likeOp) {
                    $propQ->where('title', $likeOp, "%{$q}%")
                          ->orWhere('description', $likeOp, "%{$q}%")
                          ->orWhere('property_code', $likeOp, "%{$q}%");
                })->orWhere('purpose', $likeOp, "%{$q}%");
            });
        }

        // 3. Purpose / Listing Type Filter
        $purpose = $request->get('purpose');
        if ($purpose && $purpose !== 'all') {
            $normalizedPurpose = match(strtolower($purpose)) {
                'buy', 'sale', 'for sale' => 'For Sale',
                'rent', 'for rent' => 'For Rent',
                'stay', 'short stay' => 'Short Stay',
                'land' => 'Land',
                'projects', 'development', 'new projects' => 'Development',
                default => $purpose,
            };
            $query->where('purpose', $normalizedPurpose);
        }

        // 4. Property Type Filter
        $propertyType = $request->get('property_type');
        if ($propertyType && $propertyType !== 'all') {
            $query->whereHas('property', function ($propQ) use ($propertyType) {
                $propQ->whereHas('type', function ($typeQ) use ($propertyType) {
                    $typeQ->where('code', $propertyType)->orWhere('name_en', $propertyType);
                })
                ->orWhere('type_id', $propertyType)
                ->orWhereHas('category', function ($catQ) use ($propertyType) {
                    $catQ->where('code', $propertyType)->orWhere('name_en', $propertyType);
                });
            });
        }

        // 5. Price Range Filters (Min & Max)
        $minPrice = $request->filled('min_price') ? max(0, (float)$request->min_price) : null;
        $maxPrice = $request->filled('max_price') ? max(0, (float)$request->max_price) : null;
        if ($minPrice !== null && $minPrice > 0) {
            $query->where('price', '>=', $minPrice);
        }
        if ($maxPrice !== null && $maxPrice > 0) {
            $query->where('price', '<=', $maxPrice);
        }

        // 6. Bedrooms Filter
        $bedrooms = $request->get('bedrooms');
        if ($bedrooms && $bedrooms !== 'any') {
            $beds = (int)$bedrooms;
            if ($beds > 0) {
                $query->whereHas('property', function ($propQ) use ($beds) {
                    $propQ->where('bedrooms', '>=', $beds);
                });
            }
        }

        // 7. Bathrooms Filter
        $bathrooms = $request->get('bathrooms');
        if ($bathrooms && $bathrooms !== 'any') {
            $baths = (int)$bathrooms;
            if ($baths > 0) {
                $query->whereHas('property', function ($propQ) use ($baths) {
                    $propQ->where('bathrooms', '>=', $baths);
                });
            }
        }

        // 8. Area (m²) Filters (Min & Max)
        $minArea = $request->filled('min_area') ? max(0, (float)$request->min_area) : null;
        $maxArea = $request->filled('max_area') ? max(0, (float)$request->max_area) : null;
        if ($minArea !== null && $minArea > 0) {
            $query->whereHas('property', function ($propQ) use ($minArea) {
                $propQ->where(function ($aq) use ($minArea) {
                    $aq->where('building_area', '>=', $minArea)
                       ->orWhere('land_area', '>=', $minArea);
                });
            });
        }
        if ($maxArea !== null && $maxArea > 0) {
            $query->whereHas('property', function ($propQ) use ($maxArea) {
                $propQ->where(function ($aq) use ($maxArea) {
                    $aq->where(function ($baq) use ($maxArea) {
                        $baq->whereNotNull('building_area')->where('building_area', '<=', $maxArea);
                    })->orWhere(function ($laq) use ($maxArea) {
                        $laq->whereNotNull('land_area')->where('land_area', '<=', $maxArea);
                    });
                });
            });
        }

        // 9. Parking Filter
        $parking = $request->boolean('parking');
        if ($parking) {
            $query->whereHas('property', function ($propQ) {
                $propQ->whereHas('amenities', function ($aq) {
                    $aq->where('code', 'ample_parking');
                });
            });
        }

        // 10. Amenities Filter (array or comma-separated string)
        $rawAmenities = $request->get('amenities', []);
        $amenities = is_array($rawAmenities) ? $rawAmenities : array_filter(explode(',', (string)$rawAmenities));
        if (!empty($amenities)) {
            foreach ($amenities as $amenityCode) {
                $query->whereHas('property.amenities', function ($aq) use ($amenityCode) {
                    $aq->where('code', $amenityCode);
                });
            }
        }

        // 11. Verification Filter
        $verified = $request->boolean('verified');
        if ($verified) {
            $query->whereHas('property', function ($propQ) {
                $propQ->where('status', 'verified');
            });
        }

        // 12. Sorting
        $sort = $request->get('sort', 'newest');
        match($sort) {
            'price_asc' => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            'area_desc' => $query->leftJoin('properties', 'listings.property_id', '=', 'properties.id')
                                ->orderBy('properties.building_area', 'desc')
                                ->select('listings.*'),
            'bedrooms_desc' => $query->leftJoin('properties', 'listings.property_id', '=', 'properties.id')
                                ->orderBy('properties.bedrooms', 'desc')
                                ->select('listings.*'),
            default => $query->orderBy('published_at', 'desc')->latest(),
        };

        // 13. Layout / View Mode
        $viewMode = $request->get('view', 'grid');
        if (!in_array($viewMode, ['grid', 'list', 'map'])) {
            $viewMode = 'grid';
        }

        // Map view returns 24 per page for richer map pins, grid/list returns 12
        $perPage = ($viewMode === 'map') ? 24 : 12;
        $listings = $query->paginate($perPage)->withQueryString();

        // 14. Build Applied Filter Chips
        $appliedFilters = [];
        if (!empty($q)) {
            $appliedFilters[] = [
                'key' => 'q',
                'label' => 'Keyword: ' . htmlspecialchars($q),
                'remove_param' => 'q',
            ];
        }
        if ($purpose && $purpose !== 'all') {
            $appliedFilters[] = [
                'key' => 'purpose',
                'label' => 'Type: ' . $purpose,
                'remove_param' => 'purpose',
            ];
        }
        if ($propertyType && $propertyType !== 'all') {
            $typeName = ucwords(str_replace('_', ' ', $propertyType));
            $appliedFilters[] = [
                'key' => 'property_type',
                'label' => 'Property: ' . $typeName,
                'remove_param' => 'property_type',
            ];
        }
        if ($minPrice || $maxPrice) {
            $priceLabel = 'Price: ';
            if ($minPrice && $maxPrice) {
                $priceLabel .= number_format($minPrice) . ' - ' . number_format($maxPrice) . ' ETB';
            } elseif ($minPrice) {
                $priceLabel .= 'Min ' . number_format($minPrice) . ' ETB';
            } else {
                $priceLabel .= 'Up to ' . number_format($maxPrice) . ' ETB';
            }
            $appliedFilters[] = [
                'key' => 'price',
                'label' => $priceLabel,
                'remove_params' => ['min_price', 'max_price'],
            ];
        }
        if ($bedrooms && $bedrooms !== 'any') {
            $appliedFilters[] = [
                'key' => 'bedrooms',
                'label' => "{$bedrooms}+ Beds",
                'remove_param' => 'bedrooms',
            ];
        }
        if ($bathrooms && $bathrooms !== 'any') {
            $appliedFilters[] = [
                'key' => 'bathrooms',
                'label' => "{$bathrooms}+ Baths",
                'remove_param' => 'bathrooms',
            ];
        }
        if ($minArea || $maxArea) {
            $areaLabel = 'Area: ';
            if ($minArea && $maxArea) {
                $areaLabel .= "{$minArea} - {$maxArea} m²";
            } elseif ($minArea) {
                $areaLabel .= "Min {$minArea} m²";
            } else {
                $areaLabel .= "Max {$maxArea} m²";
            }
            $appliedFilters[] = [
                'key' => 'area',
                'label' => $areaLabel,
                'remove_params' => ['min_area', 'max_area'],
            ];
        }
        if ($parking) {
            $appliedFilters[] = [
                'key' => 'parking',
                'label' => 'Reserved Parking',
                'remove_param' => 'parking',
            ];
        }
        if ($verified) {
            $appliedFilters[] = [
                'key' => 'verified',
                'label' => 'Verified Only',
                'remove_param' => 'verified',
            ];
        }
        if (!empty($amenities)) {
            foreach ($amenities as $amCode) {
                $amName = ucwords(str_replace('_', ' ', $amCode));
                $appliedFilters[] = [
                    'key' => "amenity_{$amCode}",
                    'label' => $amName,
                    'remove_amenity' => $amCode,
                ];
            }
        }

        // 15. Map Markers Data (JSON serializable)
        $mapMarkers = $listings->getCollection()->filter(function ($listing) {
            return !empty($listing->property?->latitude) && !empty($listing->property?->longitude);
        })->map(function ($listing) {
            $prop = $listing->property;
            $media = $prop->media->first();
            $cover = $media ? ($media->url ?? $media->storage_key) : 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=600&q=80';
            
            // Format shortened price for map pin (e.g., 45M ETB, 6.5K ETB)
            $p = (float)($listing->price ?? 0);
            if ($p >= 1000000) {
                $shortPrice = round($p / 1000000, 1) . 'M ETB';
            } elseif ($p >= 1000) {
                $shortPrice = round($p / 1000, 1) . 'K ETB';
            } else {
                $shortPrice = number_format($p) . ' ETB';
            }

            return [
                'id' => $listing->id,
                'property_id' => $prop->id,
                'title' => $prop->title,
                'price_formatted' => $listing->formatted_price,
                'short_price' => $shortPrice,
                'purpose' => $listing->purpose,
                'bedrooms' => $prop->bedrooms ?? 0,
                'bathrooms' => $prop->bathrooms ?? 0,
                'area' => $prop->building_area ?? $prop->land_area ?? 0,
                'lat' => (float)$prop->latitude,
                'lng' => (float)$prop->longitude,
                'image' => $cover,
                'url' => route('properties.show', $listing->id),
            ];
        })->values();

        // 16. Available Catalogs for Filter Drawer
        $availableAmenities = Amenity::where('status', 'active')->orderBy('category')->orderBy('name_en')->get();
        $propertyTypes = PropertyType::where('status', 'active')->orderBy('name_en')->get();

        return view('search.index', [
            'listings' => $listings,
            'appliedFilters' => $appliedFilters,
            'activeCount' => count($appliedFilters),
            'mapMarkers' => $mapMarkers,
            'availableAmenities' => $availableAmenities,
            'propertyTypes' => $propertyTypes,
            'viewMode' => $viewMode,
            'sort' => $sort,
            'q' => $q,
            'purpose' => $purpose,
            'propertyType' => $propertyType,
            'minPrice' => $minPrice,
            'maxPrice' => $maxPrice,
            'bedrooms' => $bedrooms,
            'bathrooms' => $bathrooms,
            'minArea' => $minArea,
            'maxArea' => $maxArea,
            'parking' => $parking,
            'amenities' => $amenities,
            'verified' => $verified,
        ]);
    }
}
