<?php

namespace App\Events\Development;

use App\Models\DevelopmentProject;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ConstructionMilestoneCertified
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public DevelopmentProject $project;

    public float $progressPercentage;

    public string $milestoneName;

    public User $certifier;

    public ?string $reportUrl;

    public function __construct(
        DevelopmentProject $project,
        float $progressPercentage,
        string $milestoneName,
        User $certifier,
        ?string $reportUrl = null
    ) {
        $this->project = $project;
        $this->progressPercentage = $progressPercentage;
        $this->milestoneName = $milestoneName;
        $this->certifier = $certifier;
        $this->reportUrl = $reportUrl;
    }
}
