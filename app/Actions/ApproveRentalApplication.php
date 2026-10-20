<?php

namespace App\Actions;

use App\Models\Lease;
use App\Models\RentalApplication;

class ApproveRentalApplication extends Action
{
    /**
     * Approve rental application and create lease record.
     */
    public function execute(RentalApplication $application, array $data): Lease
    {
        return $this->transactional(function () use ($application, $data) {
            $application->update(['status' => 'approved']);

            $lease = Lease::create([
                'property_id' => $application->property_id,
                'tenant_profile_id' => $application->tenant_profile_id,
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'rent_amount' => $data['rent_amount'],
                'deposit_amount' => $data['deposit_amount'],
                'status' => 'active',
            ]);

            return $lease;
        });
    }
}
