<?php

namespace App\Listeners;

use App\Events\Development\ConstructionMilestoneCertified;
use App\Jobs\InstallmentBillingJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class TriggerMilestoneInstallmentInvoicing implements ShouldQueue
{
    /**
     * Handle the event: trigger installment billing for buyers linked to certified milestone.
     */
    public function handle(ConstructionMilestoneCertified $event): void
    {
        Log::info("Milestone [{$event->milestoneName}] certified at {$event->progressPercentage}% for project [{$event->project->name}]. Triggering installment invoices.");

        InstallmentBillingJob::dispatch($event->project->id, $event->progressPercentage);
    }
}
