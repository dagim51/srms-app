<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use Illuminate\Http\Request;

class OfferController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        
        $offers = Offer::with(['listing.property', 'buyer:id,name'])
            ->where(function ($query) use ($user) {
                $query->where('buyer_id', $user->id)
                      ->orWhereHas('listing', function ($q) use ($user) {
                          $q->where('owner_actor_id', $user->id);
                      });
            })
            ->paginate(15);
            
        return response()->json(['data' => $offers]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'listing_id' => 'required|exists:listings,id',
            'amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'parent_offer_id' => 'nullable|exists:offers,id',
        ]);
        
        $listing = \App\Models\Listing::findOrFail($validated['listing_id']);
        
        // Cannot make an offer on your own listing
        if ($listing->owner_actor_id === auth()->id()) {
            return response()->json(['message' => 'You cannot make an offer on your own listing.'], 422);
        }
        
        $offer = Offer::create([
            'listing_id' => $validated['listing_id'],
            'buyer_id' => auth()->id(),
            'amount' => $validated['amount'],
            'notes' => $validated['notes'] ?? null,
            'parent_offer_id' => $validated['parent_offer_id'] ?? null,
            'status' => 'pending',
        ]);
        
        return response()->json(['data' => $offer], 201);
    }

    public function update(Request $request, Offer $offer)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:accepted,rejected,withdrawn',
        ]);
        
        $user = auth()->user();
        
        // Only buyer can withdraw
        if ($validated['status'] === 'withdrawn') {
            if ($offer->buyer_id !== $user->id) {
                abort(403, 'Unauthorized.');
            }
        } 
        // Only owner can accept/reject
        else {
            $listing = $offer->listing;
            if ($listing->owner_actor_id !== $user->id) {
                abort(403, 'Unauthorized.');
            }
        }
        
        // Status transitions
        if ($offer->status !== 'pending') {
            return response()->json(['message' => 'Can only update pending offers.'], 422);
        }
        
        $offer->update(['status' => $validated['status']]);
        
        return response()->json(['data' => $offer]);
    }
}
