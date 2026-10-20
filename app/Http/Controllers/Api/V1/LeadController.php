<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\CrmActivity;
use App\Models\OrganizationMember;
use App\Http\Resources\LeadResource;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        $userOrgs = OrganizationMember::where('user_id', auth()->id())
            ->pluck('organization_id');
            
        $leads = Lead::whereIn('organization_id', $userOrgs)
            ->with(['assignee:id,name', 'listing'])
            ->orderByDesc('created_at')
            ->paginate(15);
            
        return LeadResource::collection($leads);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'organization_id' => 'required|uuid|exists:organizations,id',
            'assigned_to' => 'nullable|uuid|exists:users,id',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'source' => 'nullable|string|in:web,walk_in,referral,inquiry',
            'listing_id' => 'nullable|uuid|exists:listings,id',
            'notes' => 'nullable|string',
        ]);
        
        // Enforce boundary
        $isMember = OrganizationMember::where('organization_id', $validated['organization_id'])
            ->where('user_id', auth()->id())
            ->exists();
            
        if (!$isMember) {
            abort(403, 'Unauthorized.');
        }
        
        $lead = Lead::create($validated);
        
        return (new LeadResource($lead))->response()->setStatusCode(201);
    }

    public function update(Request $request, Lead $lead)
    {
        // Enforce boundary
        $isMember = OrganizationMember::where('organization_id', $lead->organization_id)
            ->where('user_id', auth()->id())
            ->exists();
            
        if (!$isMember) {
            abort(403, 'Unauthorized.');
        }
        
        $validated = $request->validate([
            'status' => 'required|string|in:new,contacted,qualified,lost,won',
            'assigned_to' => 'nullable|uuid|exists:users,id',
        ]);
        
        $lead->update($validated);
        
        return new LeadResource($lead);
    }
    
    public function addActivity(Request $request, Lead $lead)
    {
        // Enforce boundary
        $isMember = OrganizationMember::where('organization_id', $lead->organization_id)
            ->where('user_id', auth()->id())
            ->exists();
            
        if (!$isMember) {
            abort(403, 'Unauthorized.');
        }
        
        $validated = $request->validate([
            'type' => 'required|string|in:call,email,meeting,note',
            'description' => 'nullable|string',
            'scheduled_at' => 'nullable|date',
            'completed_at' => 'nullable|date',
        ]);
        
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
