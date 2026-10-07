<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class PaymentPlan extends Model
{
    use HasUuids;

    protected $fillable = [
        'development_project_id',
        'name',
        'description'
    ];

    public function project()
    {
        return $this->belongsTo(DevelopmentProject::class, 'development_project_id');
    }

    public function installments()
    {
        return $this->hasMany(PaymentPlanInstallment::class);
    }
}
