<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
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
        'progress_percentage'
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function buildings()
    {
        return $this->hasMany(DevelopmentBuilding::class);
    }

    public function paymentPlans()
    {
        return $this->hasMany(PaymentPlan::class);
    }
}
