<?php

namespace App\Events\Trust;

use App\Models\Dispute;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DisputeResolved
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Dispute $dispute,
        public readonly string $resolverId
    ) {}
}
