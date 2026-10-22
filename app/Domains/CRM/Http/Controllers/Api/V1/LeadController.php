<?php

namespace App\Domains\CRM\Http\Controllers\Api\V1;

use App\Domains\CRM\Http\Controllers\Controller;
use App\Domains\CRM\Models\Lead;
use App\Http\Requests\Lead\AddActivityRequest;
use App\Http\Requests\Lead\StoreLeadRequest;
use App\Http\Requests\Lead\UpdateLeadRequest;
use App\Http\Resources\LeadResource;
use App\Models\OrganizationMember;

class LeadController extends Controller
{
    public function index()
    {
        $userOrgs = OrganizationMember::where('user_id', auth()->id())
            ->pluck('organization_id');

        $leads = Lead::whereIn('organization_id', $userOrgs)
            ->with(['assignee:id,name', 'listing'])
            ->orderByDesc('created_at')
            ->paginate(15);

        return LeadResource::collection($leads);
    }

    public function store(StoreLeadRequest $request)
    {
        $validated = $request->validated();
        $lead = Lead::create($validated);

        return (new LeadResource($lead))->response()->setStatusCode(201);
    }

    public function update(UpdateLeadRequest $request, Lead $lead)
    {
        $validated = $request->validated();
        $lead->update($validated);

        return new LeadResource($lead);
    }

    public function addActivity(AddActivityRequest $request, Lead $lead)
    {
        $validated = $request->validated();

        $activity = $lead->activities()->create([
            'type' => $validated['type'],
            'description' => $validated['description'] ?? null,
            'scheduled_at' => $validated['scheduled_at'] ?? null,
            'completed_at' => $validated['completed_at'] ?? null,
            'user_id' => auth()->id(),
        ]);

        return response()->json(['data' => $activity], 201);
    }
}
