<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Organization;
use App\Models\OrganizationMember;

class OrganizationController extends Controller
{
    public function index(Request $request)
    {
        $orgs = Organization::whereHas('members', function($q) {
            $q->where('user_id', auth()->id());
        })->with('branches')->get();
        
        return response()->json(['data' => $orgs]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:organizations,slug',
        ]);
        
        $organization = Organization::create([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'status' => 'active',
            'created_by' => auth()->id(),
        ]);
        
        // Attach the creator as an active member (agent/admin)
        OrganizationMember::create([
            'organization_id' => $organization->id,
            'user_id' => auth()->id(),
            'status' => 'active',
            'created_by' => auth()->id(),
        ]);
        
        return response()->json(['data' => $organization->load('members')], 201);
    }

    public function addMember(Request $request, Organization $organization)
    {
        // Enforce organizational permissions (must be member to add member)
        $isMember = OrganizationMember::where('organization_id', $organization->id)
            ->where('user_id', auth()->id())
            ->exists();
            
        if (!$isMember) {
            abort(403, 'Unauthorized. You are not a member of this organization.');
        }
        
        $validated = $request->validate([
            'user_id' => 'required|uuid|exists:users,id',
        ]);
        
        // Check duplicate
        if ($organization->members()->where('user_id', $validated['user_id'])->exists()) {
            return response()->json(['message' => 'User is already an agent of this organization.'], 409);
        }
        
        $member = OrganizationMember::create([
            'organization_id' => $organization->id,
            'user_id' => $validated['user_id'],
            'status' => 'active',
            'created_by' => auth()->id(),
        ]);
        
        return response()->json(['data' => $member], 201);
    }
}
