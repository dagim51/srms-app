<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\Offer;
use App\Services\Offers\NegotiationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

class OfferWebController extends Controller
{
    public function __construct(
        protected NegotiationService $negotiationService
    ) {}

    protected function getActiveUser()
    {
        return Auth::user();
    }

    public function index()
    {
        $user = $this->getActiveUser();
        
        $offers = Offer::with(['listing.property', 'buyer'])
            ->where(function($q) use ($user) {
                $q->where('buyer_id', $user->id)
                  ->orWhereHas('listing', function($lq) use ($user) {
                      $lq->where('owner_actor_id', $user->id);
                  });
            })
            // only show root offers in the index list to avoid clutter, or show the latest in each chain
            ->whereNull('parent_offer_id')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('dashboard.offers.index', compact('offers'));
    }

    public function create(Request $request)
    {
        $listingId = $request->query('listing_id');
        $listing = $listingId ? Listing::with('property')->findOrFail($listingId) : null;

        return view('dashboard.offers.create', compact('listing'));
    }

    public function store(Request $request)
    {
        $user = $this->getActiveUser();

        $request->validate([
            'listing_id' => 'required|exists:listings,id',
            'amount' => 'required|numeric|min:1',
            'notes' => 'nullable|string',
        ]);

        try {
            $offer = $this->negotiationService->submitOffer($user->id, [
                'listing_id' => $request->listing_id,
                'amount' => $request->amount,
                'notes' => $request->notes,
            ]);
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['amount' => $e->getMessage()]);
        }

        return redirect()->route('dashboard.offers.show', $offer)->with('success', 'Offer submitted successfully!');
    }

    public function show(Offer $offer)
    {
        $user = $this->getActiveUser();
        
        // Find the root offer
        $rootOffer = $offer;
        while ($rootOffer->parent_offer_id) {
            $rootOffer = $rootOffer->parentOffer;
        }

        $rootOffer->load(['listing.property', 'buyer', 'counterOffers.buyer']);

        // Flatten the negotiation history
        $history = collect([$rootOffer]);
        $this->loadHistory($rootOffer, $history);
        $history = $history->sortBy('created_at');

        // The latest offer in the chain determines the active status
        $latestOffer = $history->last();

        $isOwner = $latestOffer->listing->owner_actor_id === $user->id;
        $isBuyer = $latestOffer->buyer_id === $user->id;

        if (!$isOwner && !$isBuyer) {
            abort(403);
        }

        return view('dashboard.offers.show', compact('rootOffer', 'latestOffer', 'history', 'isOwner', 'isBuyer'));
    }

    private function loadHistory($offer, &$history)
    {
        foreach ($offer->counterOffers as $counter) {
            $history->push($counter);
            $this->loadHistory($counter, $history);
        }
    }

    public function update(Request $request, Offer $offer)
    {
        $user = $this->getActiveUser();
        
        $isOwner = $offer->listing->owner_actor_id === $user->id;
        $isBuyer = $offer->buyer_id === $user->id;

        if (!$isOwner && !$isBuyer) {
            abort(403);
        }

        $request->validate([
            'action' => 'required|in:accept,reject,counter',
            'counter_amount' => 'required_if:action,counter|numeric|min:1',
            'notes' => 'nullable|string',
        ]);

        if ($request->action === 'accept') {
            $this->negotiationService->acceptOffer($offer, $user->id);
            return back()->with('success', 'Offer accepted!');
        } 
        
        if ($request->action === 'reject') {
            $this->negotiationService->rejectOffer($offer, $user->id);
            return back()->with('success', 'Offer rejected.');
        } 
        
        if ($request->action === 'counter') {
            $this->negotiationService->counterOffer($offer, $user->id, (float) $request->counter_amount, $request->notes);
            return back()->with('success', 'Counteroffer sent!');
        }
    }
}
