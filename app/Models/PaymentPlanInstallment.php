<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class PaymentPlanInstallment extends Model
{
    use HasUuids;

    protected $fillable = [
        'payment_plan_id',
        'name',
        'percentage',
        'amount',
        'due_days_after_reservation'
    ];

    public function plan()
    {
        return $this->belongsTo(PaymentPlan::class, 'payment_plan_id');
    }
}
