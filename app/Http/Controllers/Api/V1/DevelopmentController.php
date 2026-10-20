<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\DevelopmentBuildingResource;
use App\Http\Resources\DevelopmentProjectResource;
use App\Http\Resources\DevelopmentUnitResource;
use App\Http\Resources\PaymentPlanResource;
use App\Models\DevelopmentBuilding;
use App\Models\DevelopmentProject;
use App\Models\OrganizationMember;
use App\Services\Development\DeveloperSalesService;
use Illuminate\Http\Request;

class DevelopmentController extends Controller
{
    public function __construct(
        protected DeveloperSalesService $developerSalesService
    ) {}

    public function index()
    {
        $userOrgs = OrganizationMember::where('user_id', auth()->id())->pluck('organization_id');
        $projects = DevelopmentProject::whereIn('organization_id', $userOrgs)
            ->with(['buildings', 'paymentPlans'])
            ->get();
        return DevelopmentProjectResource::collection($projects);
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

        $project = $this->developerSalesService->createProject(auth()->id(), $validated);
        return (new DevelopmentProjectResource($project))->response()->setStatusCode(201);
    }

    public function storeBuilding(Request $request, DevelopmentProject $project)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'total_floors' => 'nullable|integer',
            'status' => 'nullable|string'
        ]);

        $building = $this->developerSalesService->createBuilding($project, auth()->id(), $validated);
        return (new DevelopmentBuildingResource($building))->response()->setStatusCode(201);
    }

    public function storeUnit(Request $request, DevelopmentBuilding $building)
    {
        $validated = $request->validate([
            'floor_name' => 'nullable|string',
            'unit_number' => 'required|string',
            'status' => 'nullable|string|in:available,reserved,sold',
            'price' => 'nullable|numeric'
        ]);

        $unit = $this->developerSalesService->createUnit($building, auth()->id(), $validated);
        return (new DevelopmentUnitResource($unit))->response()->setStatusCode(201);
    }
    
    public function storePaymentPlan(Request $request, DevelopmentProject $project)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);

        $plan = $this->developerSalesService->createPaymentPlan($project, auth()->id(), $validated);
        return (new PaymentPlanResource($plan))->response()->setStatusCode(201);
    }
}
