<?php

namespace App\Jobs;

use App\Models\RentSchedule;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CheckOverdueRentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): int
    {
        $overdueSchedules = RentSchedule::with(['lease.tenantProfile.user', 'lease.property'])
            ->whereIn('status', ['unpaid', 'pending'])
            ->where('due_date', '<', now()->toDateString())
            ->get();

        $count = 0;

        foreach ($overdueSchedules as $schedule) {
            $schedule->update(['status' => 'overdue']);
            $count++;

            $user = $schedule->lease?->tenantProfile?->user;
            $property = $schedule->lease?->property;

            if ($user) {
                Log::warning("Overdue rent detected for tenant [{$user->name}] on property [{$property?->title}]: Amount {$schedule->amount} ETB due on {$schedule->due_date}.");
            }
        }

        return $count;
    }
}
