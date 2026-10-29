<?php

namespace App\Domains\Listing\Http\Controllers\Api\V1;

use App\Domains\Listing\Http\Controllers\Controller;
use App\Domains\Listing\Models\Listing;
use App\Http\Resources\ListingResource;
use App\Services\Listings\ListingPublisher;
use Illuminate\Http\Request;

class ListingModerationController extends Controller
{
    public function __construct(
        protected ListingPublisher $listingPublisher
    ) {}

    public function index(Request $request)
    {
        if (! $request->user()->hasAnyRole(['Administrator', 'Moderator']) && ! $request->user()->hasPermissionTo('listings.moderate')) {
            abort(403, 'Unauthorized');
        }

        $listings = Listing::where('status', 'pending_review')->with('property')->paginate(20);

        return ListingResource::collection($listings);
    }

    public function process(Request $request, Listing $listing)
    {
        if (! $request->user()->hasAnyRole(['Administrator', 'Moderator']) && ! $request->user()->hasPermissionTo('listings.moderate')) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'action' => 'required|in:approve,reject,suspend',
            'comments' => 'nullable|string',
        ]);

        $action = $validated['action'];
        $comments = $validated['comments'] ?? null;
        $moderatorId = $request->user()->id;

        $record = match ($action) {
            'approve' => $this->listingPublisher->approve($listing, $moderatorId, $comments),
            'reject' => $this->listingPublisher->reject($listing, $moderatorId, $comments),
            'suspend' => $this->listingPublisher->suspend($listing, $moderatorId, $comments),
        };

        return response()->json([
            'message' => "Listing {$action}d successfully",
            'data' => $record,
        ]);
    }
}
