<?php

namespace App\Domains\Organization\Http\Controllers\Api\V1;

use App\Domains\Organization\Http\Controllers\Controller;
use App\Domains\Organization\Http\Requests\Organization\AddMemberRequest;
use App\Domains\Organization\Http\Requests\Organization\StoreOrganizationRequest;
use App\Domains\Organization\Http\Resources\OrganizationMemberResource;
use App\Domains\Organization\Http\Resources\OrganizationResource;
use App\Domains\Organization\Models\Organization;
use App\Domains\Organization\Models\OrganizationMember;

class OrganizationController extends Controller
{
    public function index()
    {
        $orgs = Organization::whereHas('members', function ($q) {
            $q->where('user_id', auth()->id());
        })->with('branches')->get();

        return OrganizationResource::collection($orgs);
    }

    public function store(StoreOrganizationRequest $request)
    {
        $validated = $request->validated();

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

        return response()->json(['data' => new OrganizationResource($organization->load('members'))], 201);
    }

    public function addMember(AddMemberRequest $request, Organization $organization)
    {
        $validated = $request->validated();

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

        $member->load('user');

        return response()->json(['data' => new OrganizationMemberResource($member)], 201);
    }
}
