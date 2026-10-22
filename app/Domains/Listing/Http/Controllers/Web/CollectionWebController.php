<?php

namespace App\Domains\Listing\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Models\CollectionProperty;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CollectionWebController extends Controller
{
    /**
     * Resolve the currently authenticated user.
     */
    protected function getActiveUser()
    {
        return Auth::user();
    }

    /**
     * Display all collections of the user
     */
    public function index(Request $request)
    {
        $user = $this->getActiveUser();

        $collections = Collection::where('user_id', $user->id)
            ->withCount('properties')
            ->with(['properties.property.media' => fn ($q) => $q->where('visibility', 'public')->orderBy('sort_order')])
            ->latest()
            ->paginate(12);

        return view('dashboard.collections.index', compact('collections', 'user'));
    }

    /**
     * Store a new collection
     */
    public function store(Request $request)
    {
        $user = $this->getActiveUser();

        $request->validate([
            'name' => 'required|string|max:100',
            'is_private' => 'nullable|boolean',
        ]);

        $isPrivate = $request->has('is_private') ? $request->boolean('is_private') : true;

        $collection = Collection::create([
            'user_id' => $user->id,
            'name' => $request->name,
            'is_private' => $isPrivate,
        ]);

        // If an initial property ID was passed, attach it immediately
        if ($request->filled('property_id')) {
            CollectionProperty::firstOrCreate([
                'collection_id' => $collection->id,
                'property_id' => $request->property_id,
            ], [
                'sort_order' => 1,
            ]);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'collection' => $collection,
                'message' => "Collection '{$collection->name}' created successfully.",
            ], 201);
        }

        return redirect()->route('dashboard.collections.show', $collection->id)
            ->with('success', "Collection '{$collection->name}' created successfully.");
    }

    /**
     * Display a specific collection detail
     */
    public function show(Request $request, Collection $collection)
    {
        $user = $this->getActiveUser();

        // Enforce privacy isolation: private collections cannot be viewed by other users
        if ($collection->is_private && $collection->user_id !== $user->id) {
            abort(403, 'Unauthorized access to private collection.');
        }

        $collection->load([
            'properties.property.media' => fn ($q) => $q->where('visibility', 'public')->orderBy('sort_order'),
            'properties.property.listings' => fn ($q) => $q->where('status', 'published')->latest(),
            'properties.property.category',
            'properties.property.type',
        ]);

        // Other collections of the user to allow moving properties
        $otherCollections = Collection::where('user_id', $user->id)
            ->where('id', '!=', $collection->id)
            ->get();

        $isOwner = ($collection->user_id === $user->id);

        return view('dashboard.collections.show', compact('collection', 'otherCollections', 'isOwner', 'user'));
    }

    /**
     * Update collection name or privacy
     */
    public function update(Request $request, Collection $collection)
    {
        $user = $this->getActiveUser();

        if ($collection->user_id !== $user->id) {
            abort(403, 'Unauthorized.');
        }

        $request->validate([
            'name' => 'required|string|max:100',
            'is_private' => 'nullable|boolean',
        ]);

        $collection->update([
            'name' => $request->name,
            'is_private' => $request->has('is_private') ? $request->boolean('is_private') : $collection->is_private,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'collection' => $collection,
                'message' => 'Collection updated successfully.',
            ]);
        }

        return back()->with('success', 'Collection updated successfully.');
    }

    /**
     * Delete a collection
     */
    public function destroy(Request $request, Collection $collection)
    {
        $user = $this->getActiveUser();

        if ($collection->user_id !== $user->id) {
            abort(403, 'Unauthorized.');
        }

        $name = $collection->name;
        $collection->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Collection '{$name}' deleted.",
            ]);
        }

        return redirect()->route('dashboard.collections.index')
            ->with('success', "Collection '{$name}' deleted successfully.");
    }

    /**
     * Add a property to a collection
     */
    public function addProperty(Request $request, Collection $collection)
    {
        $user = $this->getActiveUser();

        if ($collection->user_id !== $user->id) {
            abort(403, 'Unauthorized.');
        }

        $request->validate([
            'property_id' => 'required|exists:properties,id',
        ]);

        $maxOrder = CollectionProperty::where('collection_id', $collection->id)->max('sort_order') ?? 0;

        $item = CollectionProperty::firstOrCreate([
            'collection_id' => $collection->id,
            'property_id' => $request->property_id,
        ], [
            'sort_order' => $maxOrder + 1,
        ]);

        $message = "Property added to '{$collection->name}'.";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'item' => $item,
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Remove a property from a collection
     */
    public function removeProperty(Request $request, Collection $collection, Property $property)
    {
        $user = $this->getActiveUser();

        if ($collection->user_id !== $user->id) {
            abort(403, 'Unauthorized.');
        }

        CollectionProperty::where('collection_id', $collection->id)
            ->where('property_id', $property->id)
            ->delete();

        $message = "Property removed from '{$collection->name}'.";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Move a property from one collection to another
     */
    public function moveProperty(Request $request, Collection $collection, Property $property)
    {
        $user = $this->getActiveUser();

        if ($collection->user_id !== $user->id) {
            abort(403, 'Unauthorized.');
        }

        $request->validate([
            'target_collection_id' => 'required|exists:collections,id',
        ]);

        $targetCollection = Collection::where('id', $request->target_collection_id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        // 1. Remove from current collection
        CollectionProperty::where('collection_id', $collection->id)
            ->where('property_id', $property->id)
            ->delete();

        // 2. Add to target collection
        $maxOrder = CollectionProperty::where('collection_id', $targetCollection->id)->max('sort_order') ?? 0;
        CollectionProperty::firstOrCreate([
            'collection_id' => $targetCollection->id,
            'property_id' => $property->id,
        ], [
            'sort_order' => $maxOrder + 1,
        ]);

        $message = "Property moved to '{$targetCollection->name}'.";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }
}
