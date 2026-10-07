<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TenantProfile;
use App\Models\RentalApplication;
use App\Models\Lease;
use App\Models\RentSchedule;
use Carbon\Carbon;

class RentalController extends Controller
{
    public function storeTenantProfile(Request $request)
    {
        $validated = $request->validate([
            'employment_status' => 'nullable|string',
            'annual_income' => 'nullable|numeric',
        ]);
        
        $profile = TenantProfile::updateOrCreate(
            ['user_id' => auth()->id()],
            $validated
        );
        
        return response()->json(['data' => $profile], 201);
    }
    
    public function storeApplication(Request $request)
    {
        $validated = $request->validate([
            'property_id' => 'required|uuid|exists:properties,id',
            'proposed_rent' => 'nullable|numeric',
            'message' => 'nullable|string'
        ]);
        
        $profile = TenantProfile::where('user_id', auth()->id())->firstOrFail();
        
        $application = RentalApplication::create([
            'property_id' => $validated['property_id'],
            'tenant_profile_id' => $profile->id,
            'status' => 'pending',
            'proposed_rent' => $validated['proposed_rent'] ?? null,
            'message' => $validated['message'] ?? null,
        ]);
        
        return response()->json(['data' => $application], 201);
    }
    
    public function approveApplication(Request $request, RentalApplication $application)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'rent_amount' => 'required|numeric',
            'deposit_amount' => 'required|numeric',
        ]);
        
        $application->update(['status' => 'approved']);
        
        $lease = Lease::create([
            'property_id' => $application->property_id,
            'tenant_profile_id' => $application->tenant_profile_id,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'rent_amount' => $validated['rent_amount'],
            'deposit_amount' => $validated['deposit_amount'],
            'status' => 'active',
        ]);
        
        // Auto-generate rent schedules for the duration (monthly)
        $start = Carbon::parse($validated['start_date']);
        $end = Carbon::parse($validated['end_date']);
        
        while ($start < $end) {
            RentSchedule::create([
                'lease_id' => $lease->id,
                'due_date' => $start->copy()->toDateString(),
                'amount' => $validated['rent_amount'],
                'status' => 'unpaid',
            ]);
            $start->addMonth();
        }
        
        return response()->json(['data' => $lease->load('schedules')], 201);
    }
}
