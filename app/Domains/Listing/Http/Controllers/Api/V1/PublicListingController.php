<?php

namespace App\Domains\Listing\Http\Controllers\Api\V1;

use App\Domains\Listing\Http\Controllers\Controller;
use App\Domains\Listing\Models\Listing;
use App\Http\Resources\ListingResource;
use Illuminate\Http\Request;

class PublicListingController extends Controller
{
    public function index(Request $request)
    {
        $query = Listing::with(['property.media', 'property.category', 'property.type'])
            ->where('status', 'published');

        if ($request->filled('purpose')) {
            $query->where('purpose', $request->purpose);
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        if ($request->filled('sort')) {
            $sort = $request->sort;
            if ($sort === 'price_asc') {
                $query->orderBy('price', 'asc');
            } elseif ($sort === 'price_desc') {
                $query->orderBy('price', 'desc');
            } elseif ($sort === 'newest') {
                $query->orderBy('published_at', 'desc');
            }
        } else {
            $query->orderBy('published_at', 'desc');
        }

        return ListingResource::collection($query->paginate($request->integer('per_page', 15)));
    }

    public function show(Listing $listing)
    {
        if ($listing->status !== 'published') {
            abort(404);
        }

        $listing->load([
            'property.media' => function ($q) {
                $q->where('visibility', 'public');
            },
            'property.category',
            'property.type',
            'property.amenities',
            'property.features',
        ]);

        return response()->json(['data' => new ListingResource($listing)]);
    }
}
