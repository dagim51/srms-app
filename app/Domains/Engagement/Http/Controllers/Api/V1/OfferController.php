<?php

namespace App\Domains\Engagement\Http\Controllers\Api\V1;

use App\Domains\Engagement\Http\Controllers\Controller;
use App\Domains\Engagement\Models\Offer;
use App\Http\Requests\Offer\StoreOfferRequest;
use App\Http\Requests\Offer\UpdateOfferRequest;
use App\Http\Resources\OfferResource;
use App\Services\Offers\NegotiationService;
use Illuminate\Auth\Access\AuthorizationException;
use InvalidArgumentException;

class OfferController extends Controller
{
    public function __construct(
        protected NegotiationService $negotiationService
    ) {}

    public function index()
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

    public function store(StoreOfferRequest $request)
    {
        $validated = $request->validated();

        try {
            $offer = $this->negotiationService->submitOffer(auth()->id(), $validated);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return (new OfferResource($offer))->response()->setStatusCode(201);
    }

    public function update(UpdateOfferRequest $request, Offer $offer)
    {
        $validated = $request->validated();
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
