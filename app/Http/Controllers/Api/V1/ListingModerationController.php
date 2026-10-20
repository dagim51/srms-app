<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Services\Listings\ListingPublisher;
use Illuminate\Http\Request;

class ListingModerationController extends Controller
{
    public function __construct(
        protected ListingPublisher $listingPublisher
    ) {}

    public function index(Request $request)
    {
        if (!$request->user()->hasRole('Administrator')) {
            abort(403, 'Unauthorized');
        }

        $listings = Listing::where('status', 'pending_review')->with('property')->paginate(20);
        return response()->json($listings);
    }

    public function process(Request $request, Listing $listing)
    {
        if (!$request->user()->hasRole('Administrator')) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'action' => 'required|in:approve,reject,suspend',
            'comments' => 'nullable|string'
        ]);

        $action = $validated['action'];
        $comments = $validated['comments'] ?? null;
        $moderatorId = $request->user()->id;

        if ($action === 'approve') {
            $listing = $this->listingPublisher->approve($listing, $moderatorId, $comments);
        } elseif ($action === 'reject') {
            $listing = $this->listingPublisher->reject($listing, $moderatorId, $comments);
        } elseif ($action === 'suspend') {
            $listing = $this->listingPublisher->suspend($listing, $moderatorId, $comments);
        }

        return response()->json(['message' => "Listing {$action}d successfully", 'listing' => $listing]);
    }
}
