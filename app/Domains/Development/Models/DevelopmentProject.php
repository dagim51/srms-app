<?php

namespace App\Domains\Development\Models;

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
}
