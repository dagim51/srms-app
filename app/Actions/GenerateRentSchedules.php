<?php

namespace App\Actions;

use App\Models\Lease;
use App\Models\RentSchedule;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class GenerateRentSchedules extends Action
{
    /**
     * Generate monthly rent schedules for a lease.
     */
    public function execute(Lease $lease): Collection
    {
        return $this->transactional(function () use ($lease) {
            $schedules = new Collection;
            $start = Carbon::parse($lease->start_date);
            $end = Carbon::parse($lease->end_date);

            while ($start < $end) {
                $schedule = RentSchedule::create([
                    'lease_id' => $lease->id,
                    'due_date' => $start->copy()->toDateString(),
                    'amount' => $lease->rent_amount,
                    'status' => 'unpaid',
                ]);

                $schedules->push($schedule);
                $start->addMonth();
            }

            return $schedules;
        });
    }
}
