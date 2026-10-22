<?php

namespace App\Domains\Development\Models;

use App\Models\PaymentPlan;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PaymentPlanInstallment extends Model
{
    use HasUuids;

    protected $fillable = [
        'payment_plan_id',
        'name',
        'percentage',
        'amount',
        'due_days_after_reservation',
    ];

    public function plan()
    {
        return $this->belongsTo(PaymentPlan::class, 'payment_plan_id');
    }
}
