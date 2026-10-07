<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DevelopmentProject;
use App\Models\DevelopmentBuilding;
use App\Models\DevelopmentUnit;
use App\Models\PaymentPlan;
use App\Models\OrganizationMember;

class DevelopmentController extends Controller
{
    private function checkAccess($orgId)
    {
        $isMember = OrganizationMember::where('organization_id', $orgId)
            ->where('user_id', auth()->id())
            ->exists();
        if (!$isMember) abort(403, 'Unauthorized.');
    }

    public function index()
    {
        $userOrgs = OrganizationMember::where('user_id', auth()->id())->pluck('organization_id');
        $projects = DevelopmentProject::whereIn('organization_id', $userOrgs)
            ->with(['buildings', 'paymentPlans'])
            ->get();
        return response()->json(['data' => $projects]);
    }

    public function storeProject(Request $request)
    {
        $validated = $request->validate([
            'organization_id' => 'required|uuid|exists:organizations,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|string|in:planning,construction,completed',
            'handover_date' => 'nullable|date',
            'progress_percentage' => 'nullable|integer|min:0|max:100'
        ]);

        $this->checkAccess($validated['organization_id']);
        $project = DevelopmentProject::create($validated);
        return response()->json(['data' => $project], 201);
    }

    public function storeBuilding(Request $request, DevelopmentProject $project)
    {
        $this->checkAccess($project->organization_id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'total_floors' => 'nullable|integer',
            'status' => 'nullable|string'
        ]);

        $building = $project->buildings()->create($validated);
        return response()->json(['data' => $building], 201);
    }

    public function storeUnit(Request $request, DevelopmentBuilding $building)
    {
        // Building -> Project -> Organization
        $project = $building->project;
        $this->checkAccess($project->organization_id);

        $validated = $request->validate([
            'floor_name' => 'nullable|string',
            'unit_number' => 'required|string',
            'status' => 'nullable|string|in:available,reserved,sold',
            'price' => 'nullable|numeric'
        ]);

        $unit = $building->units()->create($validated);
        return response()->json(['data' => $unit], 201);
    }
    
    public function storePaymentPlan(Request $request, DevelopmentProject $project)
    {
        $this->checkAccess($project->organization_id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);

        $plan = $project->paymentPlans()->create($validated);
        return response()->json(['data' => $plan], 201);
    }
}
