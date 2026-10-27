<?php

namespace App\Domains\Advertising\Http\Controllers\Api\V1;

use App\Domains\Advertising\Http\Controllers\Controller;
use App\Domains\Advertising\Models\MarketingCampaign;
use App\Domains\CRM\Models\Lead;
use App\Domains\Organization\Models\Organization;
use App\Http\Resources\LeadResource;
use App\Http\Resources\MarketingCampaignResource;
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

    public function recordImpression(Request $request, string|MarketingCampaign $campaign)
    {
        $model = $campaign instanceof MarketingCampaign ? $campaign : MarketingCampaign::findOrFail($campaign);
        $model->recordImpression();

        return response()->json([
            'status' => 'success',
            'campaign_id' => $model->id,
            'impressions_count' => (int) $model->impressions_count,
        ]);
    }

    public function recordClick(Request $request, string|MarketingCampaign $campaign)
    {
        $model = $campaign instanceof MarketingCampaign ? $campaign : MarketingCampaign::findOrFail($campaign);
        $model->recordClick();

        if ($request->isMethod('POST')) {
            return response()->json([
                'status' => 'success',
                'campaign_id' => $model->id,
                'clicks_count' => (int) $model->clicks_count,
                'target_url' => $model->target_url,
            ]);
        }

        return redirect()->away($model->target_url ?? url('/'));
    }
}
