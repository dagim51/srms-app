<?php

namespace App\Events\Rentals;

use App\Models\Lease;
use App\Models\RentalApplication;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RentalApplicationApproved
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly RentalApplication $application,
        public readonly Lease $lease
    ) {}
}
