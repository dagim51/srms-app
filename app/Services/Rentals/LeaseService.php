<?php

namespace App\Services\Rentals;

use App\Actions\ApproveRentalApplication;
use App\Actions\GenerateRentSchedules;
use App\Events\Rentals\LeaseActivated;
use App\Events\Rentals\RentalApplicationApproved;
use App\Events\Rentals\RentalApplicationSubmitted;
use App\Models\Lease;
use App\Models\RentalApplication;
use App\Models\TenantProfile;
use Illuminate\Support\Facades\DB;

class LeaseService
{
    /**
     * Store or update tenant profile.
     */
    public function storeTenantProfile(string $userId, array $data): TenantProfile
    {
        return TenantProfile::updateOrCreate(
            ['user_id' => $userId],
            $data
        );
    }

    /**
     * Submit a rental application.
     */
    public function submitApplication(string $userId, array $data): RentalApplication
    {
        return DB::transaction(function () use ($userId, $data) {
            $profile = TenantProfile::where('user_id', $userId)->firstOrFail();

            $application = RentalApplication::create([
                'property_id' => $data['property_id'],
                'tenant_profile_id' => $profile->id,
                'status' => 'pending',
                'proposed_rent' => $data['proposed_rent'] ?? null,
                'message' => $data['message'] ?? null,
            ]);

            event(new RentalApplicationSubmitted($application));

            return $application;
        });
    }

    /**
     * Approve rental application and activate lease.
     */
    public function approveApplication(RentalApplication $application, array $data): Lease
    {
        return DB::transaction(function () use ($application, $data) {
            $approveAction = new ApproveRentalApplication();
            $lease = $approveAction->execute($application, $data);

            $scheduleAction = new GenerateRentSchedules();
            $scheduleAction->execute($lease);

            event(new RentalApplicationApproved($application, $lease));
            event(new LeaseActivated($lease));

            return $lease->fresh(['schedules', 'property', 'tenantProfile']);
        });
    }

    /**
     * Terminate an active lease.
     */
    public function terminateLease(Lease $lease, ?string $reason = null): Lease
    {
        return DB::transaction(function () use ($lease, $reason) {
            $lease->status = 'terminated';
            if ($reason) {
                $lease->termination_reason = $reason;
            }
            $lease->save();

            return $lease;
        });
    }
}
