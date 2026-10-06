<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Models\CollectionProperty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CollectionController extends Controller
{
    public function index(Request $request)
    {
        $collections = Collection::where('user_id', $request->user()->id)->get();
        return response()->json(['data' => $collections]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'is_private' => 'boolean'
        ]);

        $collection = Collection::create([
            'user_id' => $request->user()->id,
            'name' => $request->name,
            'is_private' => $request->is_private ?? true
        ]);

        return response()->json(['data' => $collection], 201);
    }

    public function show(Request $request, Collection $collection)
    {
        if ($collection->is_private && $collection->user_id !== $request->user()?->id) {
            abort(403, 'Unauthorized access to private collection.');
        }

        $collection->load(['properties.property.listings' => function($q) {
            $q->where('status', 'published');
        }]);

        return response()->json([
            'data' => $collection, 
            'debug_is_private' => $collection->is_private,
            'debug_user_id' => $collection->user_id,
            'debug_req_user' => $request->user()?->id
        ]);
    }

    public function destroy(Request $request, Collection $collection)
    {
        if ($collection->user_id !== $request->user()->id) {
            abort(403);
        }

        $collection->delete();
        return response()->json(null, 204);
    }

    public function addProperty(Request $request, Collection $collection)
    {
        if ($collection->user_id !== $request->user()->id) {
            abort(403);
        }

        $request->validate(['property_id' => 'required|exists:properties,id']);

        $item = CollectionProperty::firstOrCreate([
            'collection_id' => $collection->id,
            'property_id' => $request->property_id
        ], [
            'sort_order' => CollectionProperty::where('collection_id', $collection->id)->max('sort_order') + 1
        ]);

        return response()->json(['data' => $item], 201);
    }

    public function removeProperty(Request $request, Collection $collection, $propertyId)
    {
        if ($collection->user_id !== $request->user()->id) {
            abort(403);
        }

        CollectionProperty::where('collection_id', $collection->id)
            ->where('property_id', $propertyId)
            ->delete();

        return response()->json(null, 204);
    }

    public function updateOrder(Request $request, Collection $collection)
    {
        if ($collection->user_id !== $request->user()->id) {
            abort(403);
        }

        $request->validate([
            'properties' => 'required|array',
            'properties.*' => 'exists:collection_properties,id'
        ]);

        DB::transaction(function () use ($request, $collection) {
            foreach ($request->properties as $index => $id) {
                CollectionProperty::where('collection_id', $collection->id)
                    ->where('id', $id)
                    ->update(['sort_order' => $index]);
            }
        });

        return response()->json(['message' => 'Order updated']);
    }
}
