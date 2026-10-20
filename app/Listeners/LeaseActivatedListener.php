<?php

namespace App\Listeners;

use App\Events\Rentals\LeaseActivated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class LeaseActivatedListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(LeaseActivated $event): void
    {
        $lease = $event->lease->load(['property', 'tenantProfile.user']);

        Log::info("Lease activated [{$lease->id}] for property [{$lease->property_id}].");

        // Here additional decoupled listeners can trigger tenant welcome emails,
        // sync calendar blockouts, or record deposit transactions in the ledger.
    }
}
