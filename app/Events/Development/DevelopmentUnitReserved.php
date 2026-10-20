<?php

namespace App\Events\Development;

use App\Models\DevelopmentUnit;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DevelopmentUnitReserved
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly DevelopmentUnit $unit,
        public readonly string $buyerId,
        public readonly ?float $agreedPrice = null
    ) {}
}
