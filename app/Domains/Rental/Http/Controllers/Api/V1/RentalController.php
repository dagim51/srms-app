<?php

namespace App\Domains\Rental\Http\Controllers\Api\V1;

use App\Domains\Rental\Http\Controllers\Controller;
use App\Http\Requests\Rental\ApproveApplicationRequest;
use App\Http\Requests\Rental\StoreApplicationRequest;
use App\Http\Requests\Rental\StoreTenantProfileRequest;
use App\Http\Resources\LeaseResource;
use App\Http\Resources\RentalApplicationResource;
use App\Http\Resources\TenantProfileResource;
use App\Models\RentalApplication;
use App\Services\Rentals\LeaseService;

class RentalController extends Controller
{
    public function __construct(
        protected LeaseService $leaseService
    ) {}

    public function storeTenantProfile(StoreTenantProfileRequest $request)
    {
        $validated = $request->validated();
        $profile = $this->leaseService->storeTenantProfile(auth()->id(), $validated);

        return (new TenantProfileResource($profile))->response()->setStatusCode(201);
    }

    public function storeApplication(StoreApplicationRequest $request)
    {
        $validated = $request->validated();
        $application = $this->leaseService->submitApplication(auth()->id(), $validated);

        return (new RentalApplicationResource($application))->response()->setStatusCode(201);
    }

    public function approveApplication(ApproveApplicationRequest $request, RentalApplication $application)
    {
        $validated = $request->validated();
        $lease = $this->leaseService->approveApplication($application, $validated);

        return (new LeaseResource($lease->load('schedules')))->response()->setStatusCode(201);
    }
}
