<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Models\Favorite;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriteWebController extends Controller
{
    /**
     * Resolve the currently authenticated user.
     */
    protected function getActiveUser()
    {
        return Auth::user();
    }

    /**
     * Display user's saved favorite properties
     */
    public function index(Request $request)
    {
        $user = $this->getActiveUser();

        $query = Favorite::where('user_id', $user->id)
            ->with([
                'property.media' => fn($q) => $q->where('visibility', 'public')->orderBy('sort_order'),
                'property.listings' => fn($q) => $q->where('status', 'published')->latest(),
                'property.category',
                'property.type',
                'property.amenities',
            ])
            ->latest();

        // Search within favorites
        if ($request->filled('search')) {
            $s = $request->search;
            $query->whereHas('property', function ($pq) use ($s) {
                $pq->where('title', 'like', "%{$s}%")
                   ->orWhere('description', 'like', "%{$s}%")
                   ->orWhere('property_code', 'like', "%{$s}%");
            });
        }

        // Filter by purpose
        if ($request->filled('purpose') && $request->purpose !== 'all') {
            $purpose = $request->purpose;
            $query->whereHas('property.listings', function ($lq) use ($purpose) {
                $lq->where('purpose', $purpose);
            });
        }

        // Sorting
        $sort = $request->get('sort', 'newest');
        if ($sort === 'oldest') {
            $query->oldest();
        } else {
            $query->latest();
        }

        $favorites = $query->paginate(12)->withQueryString();

        // User's collections for the "Add to Collection" dropdown / modal
        $userCollections = Collection::where('user_id', $user->id)->withCount('properties')->get();

        // Compute stats for overview
        $totalFavorites = Favorite::where('user_id', $user->id)->count();

        return view('dashboard.favorites.index', compact('favorites', 'userCollections', 'totalFavorites', 'user'));
    }

    /**
     * Toggle favorite status on a property (AJAX or form POST)
     */
    public function toggle(Request $request, Property $property)
    {
        $user = $this->getActiveUser();

        $existing = Favorite::where('user_id', $user->id)
            ->where('property_id', $property->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $favorited = false;
            $message = 'Removed from your saved favorites.';
        } else {
            // firstOrCreate ensures duplicate favorites are strictly prevented
            Favorite::firstOrCreate([
                'user_id' => $user->id,
                'property_id' => $property->id,
            ]);
            $favorited = true;
            $message = 'Added to your saved favorites.';
        }

        $count = Favorite::where('user_id', $user->id)->count();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'favorited' => $favorited,
                'count' => $count,
                'message' => $message,
                'property_id' => $property->id,
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Remove a favorite directly
     */
    public function destroy(Request $request, Favorite $favorite)
    {
        $user = $this->getActiveUser();

        if ($favorite->user_id !== $user->id) {
            abort(403, 'Unauthorized.');
        }

        $favorite->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Favorite removed successfully.',
            ]);
        }

        return back()->with('success', 'Property removed from your favorites.');
    }
}
