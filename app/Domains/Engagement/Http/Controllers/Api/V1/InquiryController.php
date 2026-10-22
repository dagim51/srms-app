<?php

namespace App\Domains\Engagement\Http\Controllers\Api\V1;

use App\Domains\Engagement\Http\Controllers\Controller;
use App\Domains\Engagement\Models\Inquiry;
use App\Http\Requests\Inquiry\StoreInquiryRequest;
use App\Http\Requests\Inquiry\UpdateInquiryStatusRequest;
use App\Http\Resources\InquiryResource;
use App\Services\Inquiries\InquiryService;

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

    public function store(StoreInquiryRequest $request)
    {
        $validated = $request->validated();

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

    public function update(UpdateInquiryStatusRequest $request, Inquiry $inquiry)
    {
        $validated = $request->validated();

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
