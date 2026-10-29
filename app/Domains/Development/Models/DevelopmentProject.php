<?php

namespace App\Domains\Development\Models;

use App\Domains\Identity\Models\User;
use App\Events\Development\ConstructionMilestoneCertified;
use App\Jobs\InstallmentBillingJob;
use App\Models\DevelopmentBuilding;
use App\Models\Organization;
use App\Models\PaymentPlan;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DevelopmentProject extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'name',
        'description',
        'status',
        'handover_date',
        'progress_percentage',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function buildings(): HasMany
    {
        return $this->hasMany(DevelopmentBuilding::class);
    }

    public function paymentPlans(): HasMany
    {
        return $this->hasMany(PaymentPlan::class);
    }

    public function updateProgress(float $percentage): int
    {
        $this->update(['progress_percentage' => $percentage]);

        $job = new InstallmentBillingJob($this->id, $percentage);

        return $job->handle();
    }

    public function certifyMilestone(string $milestoneName, float $progressPercentage, User $certifier, ?string $reportUrl = null): void
    {
        $this->update(['progress_percentage' => $progressPercentage]);

        event(new ConstructionMilestoneCertified($this, $progressPercentage, $milestoneName, $certifier, $reportUrl));
    }
}
