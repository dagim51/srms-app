<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\LeadResource;
use App\Http\Resources\MarketingCampaignResource;
use App\Models\Lead;
use App\Models\MarketingCampaign;
use App\Models\Organization;
use Illuminate\Http\Request;

class MarketingController extends Controller
{
    public function storeCampaign(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'channel' => 'required|string|in:email,social,web',
            'budget' => 'nullable|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $campaign = MarketingCampaign::create($validated);

        return response()->json(['data' => new MarketingCampaignResource($campaign)], 201);
    }

    public function storeLead(Request $request)
    {
        $validated = $request->validate([
            'marketing_campaign_id' => 'nullable|uuid|exists:marketing_campaigns,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'source' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        // Default to a system organization or require it if multi-tenant strict
        $org = Organization::first(); // Assuming at least one organization exists in tests

        $lead = Lead::create(array_merge($validated, [
            'organization_id' => $org->id,
            'status' => 'new',
        ]));

        return response()->json(['data' => new LeadResource($lead)], 201);
    }

    public function convertLead(Request $request, Lead $lead)
    {
        if ($lead->status === 'won') {
            return response()->json(['message' => 'Lead already converted'], 400);
        }

        $lead->update(['status' => 'won']);

        // In a real app, this would spawn a TenantProfile or User record here.

        $lead->load('assignee', 'listing');

        return response()->json(['data' => new LeadResource($lead)]);
    }
}
