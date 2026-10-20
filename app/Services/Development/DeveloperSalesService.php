<?php

namespace App\Services\Development;

use App\Events\Development\DevelopmentUnitReserved;
use App\Events\Development\ProjectMilestoneCompleted;
use App\Models\DevelopmentBuilding;
use App\Models\DevelopmentProject;
use App\Models\DevelopmentUnit;
use App\Models\OrganizationMember;
use App\Models\PaymentPlan;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class DeveloperSalesService
{
    /**
     * Check if user has access to organization.
     */
    public function checkAccess(string $orgId, string $userId): void
    {
        $isMember = OrganizationMember::where('organization_id', $orgId)
            ->where('user_id', $userId)
            ->exists();

        if (!$isMember) {
            throw new AuthorizationException('Unauthorized.');
        }
    }

    /**
     * Create development project.
     */
    public function createProject(string $userId, array $data): DevelopmentProject
    {
        $this->checkAccess($data['organization_id'], $userId);

        return DB::transaction(function () use ($data) {
            return DevelopmentProject::create($data);
        });
    }

    /**
     * Create building within project.
     */
    public function createBuilding(DevelopmentProject $project, string $userId, array $data): DevelopmentBuilding
    {
        $this->checkAccess($project->organization_id, $userId);

        return DB::transaction(function () use ($project, $data) {
            return $project->buildings()->create($data);
        });
    }

    /**
     * Create unit within building.
     */
    public function createUnit(DevelopmentBuilding $building, string $userId, array $data): DevelopmentUnit
    {
        $project = $building->project;
        $this->checkAccess($project->organization_id, $userId);

        return DB::transaction(function () use ($building, $data) {
            return $building->units()->create($data);
        });
    }

    /**
     * Create payment plan for project.
     */
    public function createPaymentPlan(DevelopmentProject $project, string $userId, array $data): PaymentPlan
    {
        $this->checkAccess($project->organization_id, $userId);

        return DB::transaction(function () use ($project, $data) {
            return $project->paymentPlans()->create($data);
        });
    }

    /**
     * Update project construction progress / milestone.
     */
    public function updateProgress(DevelopmentProject $project, string $userId, int $progressPercentage, ?string $milestone = null): DevelopmentProject
    {
        $this->checkAccess($project->organization_id, $userId);

        return DB::transaction(function () use ($project, $progressPercentage, $milestone) {
            $project->progress_percentage = $progressPercentage;
            if ($progressPercentage >= 100) {
                $project->status = 'completed';
            } elseif ($progressPercentage > 0 && $project->status === 'planning') {
                $project->status = 'construction';
            }
            $project->save();

            event(new ProjectMilestoneCompleted($project, $progressPercentage, $milestone));

            return $project;
        });
    }

    /**
     * Reserve a development unit.
     */
    public function reserveUnit(DevelopmentUnit $unit, string $buyerId, ?float $agreedPrice = null): DevelopmentUnit
    {
        return DB::transaction(function () use ($unit, $buyerId, $agreedPrice) {
            $unit->status = 'reserved';
            if ($agreedPrice !== null) {
                $unit->price = $agreedPrice;
            }
            $unit->save();

            event(new DevelopmentUnitReserved($unit, $buyerId, $agreedPrice));

            return $unit;
        });
    }
}
