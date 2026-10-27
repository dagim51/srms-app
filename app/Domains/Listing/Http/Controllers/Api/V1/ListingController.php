<?php

namespace App\Domains\Listing\Http\Controllers\Api\V1;

use App\Domains\Listing\Http\Controllers\Controller;
use App\Domains\Listing\Models\Listing;
use App\Http\Resources\ListingResource;
use App\Models\Property;
use App\Services\Listing\ListingPromotionService;
use App\Services\Listings\ListingPublisher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ListingController extends Controller
{
    protected ListingPublisher $publisher;

    public function __construct(ListingPublisher $publisher)
    {
        $this->publisher = $publisher;
    }

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
            'owner_actor_id' => $request->user()->id,
        ]);

        return response()->json(['data' => new ListingResource($listing)], 201);
    }

    public function storeDirect(Request $request)
    {
        $validated = $request->validate([
            'property_id' => 'required|uuid|exists:properties,id',
            'purpose' => 'required|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|size:3',
        ]);

        $property = Property::findOrFail($validated['property_id']);
        Gate::authorize('update', $property);

        $listing = $property->listings()->create([
            'purpose' => $validated['purpose'],
            'price' => $validated['price'] ?? null,
            'currency' => $validated['currency'] ?? 'ETB',
            'status' => 'draft',
            'owner_actor_id' => $request->user()->id,
        ]);

        return response()->json(['data' => new ListingResource($listing)], 201);
    }

    public function show(Listing $listing)
    {
        $listing->load(['property', 'ownerActor', 'moderations', 'searchIndex']);

        return response()->json(['data' => new ListingResource($listing)]);
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

        return response()->json(['data' => new ListingResource($listing)]);
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

        $listing = $this->publisher->submitForReview($listing);

        return response()->json(['message' => 'Listing submitted for review', 'data' => new ListingResource($listing)]);
    }

    public function updateStatus(Request $request, Listing $listing)
    {
        Gate::authorize('update', $listing);

        $validated = $request->validate([
            'status' => 'required|in:draft,pending_review,approved,published,paused,rejected,expired,suspended,archived',
        ]);

        $newStatus = $validated['status'];

        if ($newStatus === 'published') {
            if (empty($listing->price) || empty($listing->purpose)) {
                return response()->json(['message' => 'Mandatory fields missing for publishing'], 422);
            }
            $listing = $this->publisher->publish($listing, $request->user()?->id);
        } elseif ($newStatus === 'paused' && $listing->status === 'published') {
            $listing = $this->publisher->unpublish($listing, $request->user()?->id);
        } else {
            $listing->status = $newStatus;
            $listing->save();
        }

        return response()->json(['data' => new ListingResource($listing)]);
    }

    public function promote(Request $request, Listing $listing, ListingPromotionService $promotionService)
    {
        Gate::authorize('update', $listing);

        $validated = $request->validate([
            'tier' => 'required|string|in:basic,featured,premium,urgent',
            'duration_days' => 'nullable|integer|min:1|max:90',
            'amount_paid' => 'nullable|numeric|min:0',
        ]);

        $promotion = $promotionService->promote(
            $listing,
            $validated['tier'],
            $validated['duration_days'] ?? 7,
            $validated['amount_paid'] ?? null,
            $request->user()?->id
        );

        return response()->json([
            'message' => 'Listing successfully boosted with promotion.',
            'data' => $promotion,
        ], 201);
    }
}
