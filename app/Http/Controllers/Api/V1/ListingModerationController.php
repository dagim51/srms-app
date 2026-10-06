<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Listing;
use Illuminate\Support\Facades\Gate;

class ListingModerationController extends Controller
{
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
        
        if ($action === 'approve') {
            $listing->status = 'approved';
        } elseif ($action === 'reject') {
            $listing->status = 'rejected';
        } elseif ($action === 'suspend') {
            $listing->status = 'suspended';
        }

        $listing->save();

        $listing->moderations()->create([
            'moderator_id' => $request->user()->id,
            'action' => $action,
            'comments' => $validated['comments'] ?? null
        ]);

        return response()->json(['message' => "Listing {$action}d successfully", 'listing' => $listing]);
    }
}
