<?php

namespace App\Domains\CRM\Models;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class CommissionRule extends Model
{
    use HasUuids;

    protected $fillable = [
        'organization_id',
        'rule_type',
        'percentage',
        'fixed_amount',
        'min_deal_amount',
        'max_deal_amount',
        'is_active',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
