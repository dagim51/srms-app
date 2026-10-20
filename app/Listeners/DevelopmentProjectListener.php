<?php

namespace App\Listeners;

use App\Events\Development\DevelopmentUnitReserved;
use App\Events\Development\ProjectMilestoneCompleted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class DevelopmentProjectListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        if ($event instanceof ProjectMilestoneCompleted) {
            Log::info("Development project [{$event->project->id}] reached {$event->progressPercentage}% milestone ({$event->milestone}).");
        } elseif ($event instanceof DevelopmentUnitReserved) {
            Log::info("Development unit [{$event->unit->id}] reserved by buyer [{$event->buyerId}] at price [{$event->agreedPrice}].");
        }
    }
}
