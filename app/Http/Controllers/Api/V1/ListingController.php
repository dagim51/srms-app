<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ListingController extends Controller
{
    public function store(Request $request, Property $property)
    {
        Gate::authorize('update', $property); // Only property owner can create listing

        $validated = $request->validate([
            'purpose' => 'required|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|size:3',
        ]);

        $listing = $property->listings()->create([
            'purpose' => $validated['purpose'],
            'price' => $validated['price'] ?? null,
            'currency' => $validated['currency'] ?? 'ETB',
            'status' => 'draft',
            'owner_actor_id' => $request->user()->id
        ]);

        return response()->json($listing, 201);
    }

    public function update(Request $request, Listing $listing)
    {
        Gate::authorize('update', $listing);

        $validated = $request->validate([
            'purpose' => 'sometimes|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|size:3',
        ]);

        $listing->update($validated);

        return response()->json($listing);
    }

    public function submit(Request $request, Listing $listing)
    {
        Gate::authorize('update', $listing);

        if ($listing->status !== 'draft' && $listing->status !== 'rejected') {
            return response()->json(['message' => 'Only draft or rejected listings can be submitted'], 422);
        }

        // Automated validation before entering queue
        if (empty($listing->price) || empty($listing->purpose)) {
            return response()->json(['message' => 'Mandatory fields missing for submission'], 422);
        }

        $listing->status = 'pending_review';
        $listing->save();

        return response()->json(['message' => 'Listing submitted for review', 'listing' => $listing]);
    }

    public function updateStatus(Request $request, Listing $listing)
    {
        Gate::authorize('update', $listing);

        $validated = $request->validate([
            'status' => 'required|in:draft,pending_review,approved,published,paused,rejected,expired,suspended,archived'
        ]);

        $newStatus = $validated['status'];

        if ($newStatus === 'published') {
            if (empty($listing->price) || empty($listing->purpose)) {
                return response()->json(['message' => 'Mandatory fields missing for publishing'], 422);
            }
            $listing->published_at = now();
        }

        $listing->status = $newStatus;
        $listing->save();

        return response()->json($listing);
    }
}
