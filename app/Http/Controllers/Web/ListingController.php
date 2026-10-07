<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ListingController extends Controller
{
    /**
     * Display a paginated listing directory with filters, counters, and quality indicators.
     */
    public function index(Request $request)
    {
        $query = Listing::with(['property.media', 'property.category', 'property.type'])->latest();

        // Status Filter
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Purpose / Mode Filter
        if ($request->filled('purpose') && $request->purpose !== 'all') {
            $query->where('purpose', $request->purpose);
        }

        // Search by Title, Property Code
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('property', function ($pq) use ($search) {
                    $pq->where('title', 'like', "%{$search}%")
                       ->orWhere('property_code', 'like', "%{$search}%");
                })->orWhere('purpose', 'like', "%{$search}%")
                  ->orWhere('currency', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sort = $request->get('sort', 'latest');
        if ($sort === 'price_asc') {
            $query->orderBy('price', 'asc');
        } elseif ($sort === 'price_desc') {
            $query->orderBy('price', 'desc');
        } else {
            $query->latest();
        }

        $listings = $query->paginate(12)->withQueryString();

        // Calculate KPI status counters
        $stats = [
            'total' => Listing::count(),
            'published' => Listing::where('status', 'published')->count(),
            'draft' => Listing::where('status', 'draft')->count(),
            'pending' => Listing::where('status', 'pending_review')->count(),
            'paused' => Listing::where('status', 'paused')->count(),
        ];

        return view('dashboard.listings.index', compact('listings', 'stats'));
    }

    /**
     * Show the step-by-step listing creation wizard linking to a canonical property.
     */
    public function create(Request $request)
    {
        $selectedPropertyId = $request->get('property_id');
        
        $properties = Property::with(['media', 'amenities', 'features', 'category', 'type'])
            ->latest()
            ->get();

        $selectedProperty = $selectedPropertyId 
            ? $properties->firstWhere('id', $selectedPropertyId) 
            : null;

        return view('dashboard.listings.create', compact('properties', 'selectedProperty'));
    }

    /**
     * Store a new listing linked to canonical property.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'property_id' => 'required|uuid|exists:properties,id',
            'purpose' => 'required|string|in:For Sale,For Rent,Short Stay,Land,Development',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3|in:ETB,USD,EUR',
            'visibility' => 'nullable|string|in:public,private,unlisted',
            'action' => 'nullable|string|in:draft,publish',
            'expires_at' => 'nullable|date',
            // Optional marketing notes
            'marketing_headline' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $status = ($request->input('action') === 'draft') ? 'draft' : 'published';

        $listing = Listing::create([
            'property_id' => $validated['property_id'],
            'purpose' => $validated['purpose'],
            'price' => $validated['price'],
            'currency' => $validated['currency'],
            'status' => $status,
            'visibility' => $validated['visibility'] ?? 'public',
            'owner_actor_id' => Auth::id() ?? null,
            'published_at' => ($status === 'published') ? now() : null,
            'expires_at' => $validated['expires_at'] ?? null,
        ]);

        $msg = ($status === 'draft') 
            ? 'Listing saved as draft successfully.' 
            : 'Listing created and published to marketplace successfully!';

        return redirect()->route('dashboard.listings.index')->with('success', $msg);
    }

    /**
     * Show the listing editor.
     */
    public function edit(Listing $listing)
    {
        $listing->load(['property.media', 'property.amenities', 'property.features', 'property.category', 'property.type', 'moderations']);
        
        return view('dashboard.listings.edit', compact('listing'));
    }

    /**
     * Update the specified listing.
     */
    public function update(Request $request, Listing $listing)
    {
        $validated = $request->validate([
            'purpose' => 'required|string|in:For Sale,For Rent,Short Stay,Land,Development',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3|in:ETB,USD,EUR',
            'status' => 'required|string|in:draft,pending_review,approved,published,paused,rejected,suspended,archived',
            'visibility' => 'required|string|in:public,private,unlisted',
            'expires_at' => 'nullable|date',
        ]);

        if ($validated['status'] === 'published' && !$listing->published_at) {
            $validated['published_at'] = now();
        }

        $listing->update($validated);

        return redirect()->route('dashboard.listings.index')
            ->with('success', "Listing '{$listing->property->title}' updated successfully.");
    }

    /**
     * Toggle status between published and paused or publish draft.
     */
    public function toggleStatus(Request $request, Listing $listing)
    {
        $target = $request->input('status');

        if ($target && in_array($target, ['published', 'paused', 'draft', 'pending_review'])) {
            $listing->status = $target;
            if ($target === 'published' && !$listing->published_at) {
                $listing->published_at = now();
            }
            $listing->save();
            return back()->with('success', "Listing status updated to " . ucfirst(str_replace('_', ' ', $target)));
        }

        // Simple toggle
        if ($listing->status === 'published') {
            $listing->status = 'paused';
        } else {
            $listing->status = 'published';
            if (!$listing->published_at) {
                $listing->published_at = now();
            }
        }
        $listing->save();

        return back()->with('success', "Listing status changed to " . ucfirst($listing->status));
    }

    /**
     * Delete the listing (soft delete).
     */
    public function destroy(Listing $listing)
    {
        $listing->delete();
        return redirect()->route('dashboard.listings.index')
            ->with('success', 'Listing removed successfully.');
    }

    /**
     * Interactive public preview of how the listing appears to clients.
     */
    public function preview(Listing $listing)
    {
        $listing->load(['property.media', 'property.amenities', 'property.features', 'property.category', 'property.type']);

        return view('dashboard.listings.preview', compact('listing'));
    }
}
