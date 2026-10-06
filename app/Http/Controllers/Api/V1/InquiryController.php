<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use Illuminate\Http\Request;

class InquiryController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        $inquiries = Inquiry::where('requester_id', $user->id)
            ->orWhere('seller_agent_id', $user->id)
            ->with(['listing', 'property', 'requester', 'sellerAgent'])
            ->get();
            
        return response()->json(['data' => $inquiries]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'listing_id' => 'required|exists:listings,id',
            'message' => 'nullable|string',
        ]);
        
        // Use full namespace for Listing to avoid import issues
        $listing = \App\Models\Listing::findOrFail($validated['listing_id']);
        
        $inquiry = Inquiry::create([
            'listing_id' => $listing->id,
            'property_id' => $listing->property_id,
            'requester_id' => auth()->id(),
            'seller_agent_id' => $listing->owner_actor_id,
            'status' => 'pending',
            'message' => $validated['message'] ?? null,
        ]);
        
        return response()->json(['data' => $inquiry], 201);
    }

    public function show(Inquiry $inquiry)
    {
        $user = auth()->user();
        
        if ($inquiry->requester_id !== $user->id && $inquiry->seller_agent_id !== $user->id) {
            abort(403, 'Unauthorized access to inquiry.');
        }
        
        $inquiry->load(['listing', 'property', 'requester', 'sellerAgent']);
        
        return response()->json(['data' => $inquiry]);
    }

    public function update(Request $request, Inquiry $inquiry)
    {
        $user = auth()->user();
        
        if ($inquiry->seller_agent_id !== $user->id) {
            abort(403, 'Only the seller/agent can update inquiry status.');
        }
        
        $validated = $request->validate([
            'status' => 'required|in:pending,responded,closed',
        ]);
        
        $inquiry->update($validated);
        
        return response()->json(['data' => $inquiry]);
    }

    public function destroy(Inquiry $inquiry)
    {
        $user = auth()->user();
        
        if ($inquiry->requester_id !== $user->id) {
            abort(403, 'Only the requester can delete the inquiry.');
        }
        
        $inquiry->delete();
        
        return response()->noContent();
    }
}
