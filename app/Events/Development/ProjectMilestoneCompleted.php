<?php

namespace App\Events\Development;

use App\Models\DevelopmentProject;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProjectMilestoneCompleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly DevelopmentProject $project,
        public readonly int $progressPercentage,
        public readonly ?string $milestone = null
    ) {}
}
