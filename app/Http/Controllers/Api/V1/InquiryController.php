<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\InquiryResource;
use App\Models\Inquiry;
use App\Services\Inquiries\InquiryService;
use Illuminate\Http\Request;

class InquiryController extends Controller
{
    protected InquiryService $inquiryService;

    public function __construct(InquiryService $inquiryService)
    {
        $this->inquiryService = $inquiryService;
    }

    public function index()
    {
        $user = auth()->user();
        
        $inquiries = Inquiry::where('requester_id', $user->id)
            ->orWhere('seller_agent_id', $user->id)
            ->with(['listing', 'property', 'requester', 'sellerAgent'])
            ->get();
            
        return response()->json(['data' => InquiryResource::collection($inquiries)]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'listing_id' => 'required|exists:listings,id',
            'message' => 'nullable|string',
        ]);
        
        try {
            $inquiry = $this->inquiryService->create($validated, auth()->id());
            return response()->json(['data' => new InquiryResource($inquiry)], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function show(Inquiry $inquiry)
    {
        $user = auth()->user();
        
        if ($inquiry->requester_id !== $user->id && $inquiry->seller_agent_id !== $user->id) {
            abort(403, 'Unauthorized access to inquiry.');
        }
        
        $inquiry->load(['listing', 'property', 'requester', 'sellerAgent']);
        
        return response()->json(['data' => new InquiryResource($inquiry)]);
    }

    public function update(Request $request, Inquiry $inquiry)
    {
        $user = auth()->user();
        
        if ($inquiry->seller_agent_id !== $user->id) {
            abort(403, 'Only the seller/agent can update inquiry status.');
        }
        
        $validated = $request->validate([
            'status' => 'required|in:pending,responded,closed',
            'response' => 'nullable|string',
        ]);
        
        try {
            if ($validated['status'] === 'responded') {
                $inquiry = $this->inquiryService->respond($inquiry, $validated['response'] ?? '');
            } elseif ($validated['status'] === 'closed') {
                $inquiry = $this->inquiryService->close($inquiry);
            } else {
                $inquiry->update(['status' => $validated['status']]);
            }
            
            return response()->json(['data' => new InquiryResource($inquiry)]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
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