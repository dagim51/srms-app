<?php

namespace App\Domains\Development\Http\Controllers\Api\V1;

use App\Domains\Development\Http\Controllers\Controller;
use App\Domains\Development\Models\DevelopmentBuilding;
use App\Domains\Development\Models\DevelopmentProject;
use App\Http\Requests\Development\StoreBuildingRequest;
use App\Http\Requests\Development\StorePaymentPlanRequest;
use App\Http\Requests\Development\StoreProjectRequest;
use App\Http\Requests\Development\StoreUnitRequest;
use App\Http\Resources\DevelopmentBuildingResource;
use App\Http\Resources\DevelopmentProjectResource;
use App\Http\Resources\DevelopmentUnitResource;
use App\Http\Resources\PaymentPlanResource;
use App\Models\OrganizationMember;
use App\Services\Development\DeveloperSalesService;

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

    public function storeProject(StoreProjectRequest $request)
    {
        $validated = $request->validated();
        $project = $this->developerSalesService->createProject(auth()->id(), $validated);

        return (new DevelopmentProjectResource($project))->response()->setStatusCode(201);
    }

    public function storeBuilding(StoreBuildingRequest $request, DevelopmentProject $project)
    {
        $validated = $request->validated();
        $building = $this->developerSalesService->createBuilding($project, auth()->id(), $validated);

        return (new DevelopmentBuildingResource($building))->response()->setStatusCode(201);
    }

    public function storeUnit(StoreUnitRequest $request, $building)
    {
        $building = DevelopmentBuilding::findOrFail($building);
        $validated = $request->validated();
        $unit = $this->developerSalesService->createUnit($building, auth()->id(), $validated);

        return (new DevelopmentUnitResource($unit))->response()->setStatusCode(201);
    }

    public function storePaymentPlan(StorePaymentPlanRequest $request, DevelopmentProject $project)
    {
        $validated = $request->validated();
        $plan = $this->developerSalesService->createPaymentPlan($project, auth()->id(), $validated);

        return (new PaymentPlanResource($plan))->response()->setStatusCode(201);
    }
}
