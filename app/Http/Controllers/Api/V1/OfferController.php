<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\OfferResource;
use App\Models\Offer;
use App\Services\Offers\NegotiationService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use InvalidArgumentException;

class OfferController extends Controller
{
    public function __construct(
        protected NegotiationService $negotiationService
    ) {}

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
            
        return OfferResource::collection($offers);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'listing_id' => 'required|exists:listings,id',
            'amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'parent_offer_id' => 'nullable|exists:offers,id',
            'expires_at' => 'nullable|date',
        ]);
        
        try {
            $offer = $this->negotiationService->submitOffer(auth()->id(), $validated);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
        
        return (new OfferResource($offer))->response()->setStatusCode(201);
    }

    public function update(Request $request, Offer $offer)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:accepted,rejected,withdrawn',
        ]);
        
        $user = auth()->user();
        
        try {
            if ($validated['status'] === 'withdrawn') {
                $offer = $this->negotiationService->withdrawOffer($offer, $user->id);
            } elseif ($validated['status'] === 'accepted') {
                $offer = $this->negotiationService->acceptOffer($offer, $user->id);
            } elseif ($validated['status'] === 'rejected') {
                $offer = $this->negotiationService->rejectOffer($offer, $user->id);
            }
        } catch (AuthorizationException $e) {
            abort(403, 'Unauthorized.');
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
        
        return new OfferResource($offer);
    }
}
