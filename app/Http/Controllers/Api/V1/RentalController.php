<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\LeaseResource;
use App\Http\Resources\RentalApplicationResource;
use App\Http\Resources\TenantProfileResource;
use App\Models\RentalApplication;
use App\Services\Rentals\LeaseService;
use Illuminate\Http\Request;

class RentalController extends Controller
{
    public function __construct(
        protected LeaseService $leaseService
    ) {}

    public function storeTenantProfile(Request $request)
    {
        $validated = $request->validate([
            'employment_status' => 'nullable|string',
            'annual_income' => 'nullable|numeric',
        ]);
        
        $profile = $this->leaseService->storeTenantProfile(auth()->id(), $validated);
        
        return (new TenantProfileResource($profile))->response()->setStatusCode(201);
    }
    
    public function storeApplication(Request $request)
    {
        $validated = $request->validate([
            'property_id' => 'required|uuid|exists:properties,id',
            'proposed_rent' => 'nullable|numeric',
            'message' => 'nullable|string'
        ]);
        
        $application = $this->leaseService->submitApplication(auth()->id(), $validated);
        
        return (new RentalApplicationResource($application))->response()->setStatusCode(201);
    }
    
    public function approveApplication(Request $request, RentalApplication $application)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'rent_amount' => 'required|numeric',
            'deposit_amount' => 'required|numeric',
        ]);
        
        $lease = $this->leaseService->approveApplication($application, $validated);
        
        return (new LeaseResource($lease->load('schedules')))->response()->setStatusCode(201);
    }
}
