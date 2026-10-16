<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\Property;
use App\Support\EthiopicTextNormalizer;
use Illuminate\Http\Request;

class PublicPropertyWebController extends Controller
{
    /**
     * Public Marketplace Home Page
     */
    public function home()
    {
        // 1. Featured Properties (Published listings with high quality / media)
        $featuredListings = Listing::with(['property.media', 'property.category', 'property.type'])
            ->where('status', 'published')
            ->where('visibility', 'public')
            ->latest('published_at')
            ->take(6)
            ->get();


        // 3. New Developments (Off-plan / projects)
        $newDevelopments = Listing::with(['property.media', 'property.category', 'property.type'])
            ->where('status', 'published')
            ->where('visibility', 'public')
            ->where('purpose', 'Development')
            ->latest('published_at')
            ->take(4)
            ->get();

        // 4. Popular Cities in Ethiopia
        $popularCities = [
            [
                'name' => 'Addis Ababa',
                'amharic' => 'አዲስ አበባ',
                'description' => 'The diplomatic capital & vibrant commercial hub',
                'image' => 'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?auto=format&fit=crop&w=600&q=80',
                'count' => Listing::where('status', 'published')->count(),
                'query' => 'Addis Ababa',
            ],
            [
                'name' => 'Hawassa',
                'amharic' => 'ሀዋሳ',
                'description' => 'Lakeside resort living and fast-growing industrial hub',
                'image' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=600&q=80',
                'count' => 14,
                'query' => 'Hawassa',
            ],
            [
                'name' => 'Bahir Dar',
                'amharic' => 'ባሕር ዳር',
                'description' => 'Lake Tana palm-lined boulevards and cultural heritage',
                'image' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=600&q=80',
                'count' => 9,
                'query' => 'Bahir Dar',
            ],
            [
                'name' => 'Bishoftu',
                'amharic' => 'ቢሾፍቱ',
                'description' => 'Crater lakes weekend getaways & scenic luxury villas',
                'image' => 'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?auto=format&fit=crop&w=600&q=80',
                'count' => 11,
                'query' => 'Bishoftu',
            ],
            [
                'name' => 'Adama',
                'amharic' => 'አዳማ',
                'description' => 'Major expressway junction & bustling business center',
                'image' => 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=600&q=80',
                'count' => 7,
                'query' => 'Adama',
            ],
            [
                'name' => 'Dire Dawa',
                'amharic' => 'ድሬዳዋ',
                'description' => 'Historic chartered city & eastern trade crossway',
                'image' => 'https://images.unsplash.com/photo-1477959858617-67f30bc75b82?auto=format&fit=crop&w=600&q=80',
                'count' => 5,
                'query' => 'Dire Dawa',
            ],
        ];

        return view('welcome', compact('featuredListings', 'newDevelopments', 'popularCities'));
    }

    /**
     * Public Property Discovery Directory (`/properties`)
     */
    public function index(Request $request)
    {
        $query = Listing::with([
            'property.media' => fn($q) => $q->where('visibility', 'public')->orderBy('sort_order'),
            'property.category',
            'property.type',
        ])
        ->where('status', 'published')
        ->where('visibility', 'public');

        // Mode / Purpose Filter (Buy, Rent, Stay, Land, Projects)
        if ($request->filled('purpose') && $request->purpose !== 'all') {
            $purpose = $request->purpose;
            // Support URL aliases
            if ($purpose === 'buy' || $purpose === 'sale') $purpose = 'For Sale';
            elseif ($purpose === 'rent') $purpose = 'For Rent';
            elseif ($purpose === 'stay') $purpose = 'Short Stay';
            elseif ($purpose === 'projects') $purpose = 'Development';
            $query->where('purpose', $purpose);
        }

        $likeOp = \Illuminate\Support\Facades\DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

        // Search Keyword (City, Subcity, Neighborhood, Title)
        if ($request->filled('search')) {
            $s = $request->search;
            $tokens = EthiopicTextNormalizer::getSearchTokens($s);
            $query->where(function ($q) use ($tokens, $likeOp) {
                $q->whereHas('property', function ($pq) use ($tokens, $likeOp) {
                    $pq->where(function ($sub) use ($tokens, $likeOp) {
                        foreach ($tokens as $token) {
                            $sub->orWhere('title', $likeOp, "%{$token}%")
                                ->orWhere('description', $likeOp, "%{$token}%")
                                ->orWhere('property_code', $likeOp, "%{$token}%");
                        }
                    });
                })->orWhere(function ($sub) use ($tokens, $likeOp) {
                    foreach ($tokens as $token) {
                        $sub->orWhere('purpose', $likeOp, "%{$token}%");
                    }
                });
            });
        }

        // Location / City Filter
        if ($request->filled('location')) {
            $loc = $request->location;
            $locTokens = EthiopicTextNormalizer::getSearchTokens($loc);
            $query->whereHas('property', function ($pq) use ($locTokens, $likeOp) {
                $pq->where(function ($sub) use ($locTokens, $likeOp) {
                    foreach ($locTokens as $token) {
                        $sub->orWhere('title', $likeOp, "%{$token}%")
                            ->orWhere('description', $likeOp, "%{$token}%");
                    }
                });
            });
        }

        // Price Range Filters
        if ($request->filled('min_price')) {
            $query->where('price', '>=', (float)$request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', (float)$request->max_price);
        }

        // Bedroom Filter
        if ($request->filled('bedrooms') && $request->bedrooms !== 'any') {
            $beds = (int)$request->bedrooms;
            $query->whereHas('property', function ($pq) use ($beds) {
                if ($beds >= 4) {
                    $pq->where('bedrooms', '>=', 4);
                } else {
                    $pq->where('bedrooms', $beds);
                }
            });
        }

        // Sorting
        $sort = $request->get('sort', 'newest');
        if ($sort === 'price_asc') {
            $query->orderBy('price', 'asc');
        } elseif ($sort === 'price_desc') {
            $query->orderBy('price', 'desc');
        } else {
            $query->orderBy('published_at', 'desc')->latest();
        }

        $listings = $query->paginate(12)->withQueryString();

        return view('marketplace.index', compact('listings'));
    }

    /**
     * Public Short-Stay & Vacation Accommodations Directory (`/short-stay`)
     */
    public function shortStay(Request $request)
    {
        $request->merge(['purpose' => 'Short Stay']);
        return $this->index($request);
    }

    /**
     * Public Property Detail Page (`/properties/{id}`)
     */
    public function show($id)
    {
        // Find by listing ID or property ID
        $listing = Listing::where(function ($q) use ($id) {
            $q->where('id', $id)->orWhere('property_id', $id);
        })
        ->where('status', 'published')
        ->where('visibility', 'public')
        ->with([
            'property.media' => fn($q) => $q->where('visibility', 'public')->orderBy('sort_order'),
            'property.amenities',
            'property.features',
            'property.category',
            'property.type',
        ])
        ->firstOrFail();

        // 3 Similar / Nearby Properties
        $similarListings = Listing::with(['property.media', 'property.category'])
            ->where('status', 'published')
            ->where('visibility', 'public')
            ->where('id', '!=', $listing->id)
            ->where('purpose', $listing->purpose)
            ->take(3)
            ->get();

        return view('marketplace.show', compact('listing', 'similarListings'));
    }
}
