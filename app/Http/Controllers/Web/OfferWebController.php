<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OfferWebController extends Controller
{
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

        $listing = Listing::findOrFail($request->listing_id);

        if ($listing->owner_actor_id === $user->id) {
            return back()->withErrors(['amount' => 'You cannot make an offer on your own listing.']);
        }

        $offer = Offer::create([
            'listing_id' => $listing->id,
            'buyer_id' => $user->id,
            'amount' => $request->amount,
            'status' => 'pending',
            'notes' => $request->notes,
        ]);

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
            $offer->update(['status' => 'accepted']);
            return back()->with('success', 'Offer accepted!');
        } 
        
        if ($request->action === 'reject') {
            $offer->update(['status' => 'rejected']);
            return back()->with('success', 'Offer rejected.');
        } 
        
        if ($request->action === 'counter') {
            // Mark current as countered
            $offer->update(['status' => 'countered']);

            // The buyer ID for the counteroffer is the original buyer, the agent is acting on the listing
            // Wait, if agent counters, the buyer is still the same.
            
            $newOffer = Offer::create([
                'listing_id' => $offer->listing_id,
                'buyer_id' => $offer->buyer_id, // Always keep the original buyer
                'parent_offer_id' => $offer->id,
                'amount' => $request->counter_amount,
                'status' => 'pending',
                'notes' => $request->notes,
                // We could use an 'actor_id' if we wanted to track who made the specific offer, 
                // but we can infer: if it's the seller countering, the offer amount changes, but how do we know who made it?
                // For this mock, we can just say if it's not the buyer, it's the seller.
            ]);

            return back()->with('success', 'Counteroffer sent!');
        }
    }
}
