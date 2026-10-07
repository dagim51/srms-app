import os

def write_file(path, content):
    with open(path, 'w', encoding='utf-8') as f:
        f.write(content)

models_dir = 'c:\\Users\\dell\\Desktop\\SRMS\\srms-app\\app\\Models\\'

write_file(models_dir + 'DevelopmentProject.php', """<?php
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
""")

write_file(models_dir + 'DevelopmentBuilding.php', """<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class DevelopmentBuilding extends Model
{
    use HasUuids;

    protected $fillable = [
        'development_project_id',
        'name',
        'total_floors',
        'status',
        'progress_percentage'
    ];

    public function project()
    {
        return $this->belongsTo(DevelopmentProject::class, 'development_project_id');
    }

    public function units()
    {
        return $this->hasMany(DevelopmentUnit::class);
    }
}
""")

write_file(models_dir + 'DevelopmentUnit.php', """<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class DevelopmentUnit extends Model
{
    use HasUuids;

    protected $fillable = [
        'development_building_id',
        'floor_name',
        'unit_number',
        'status',
        'price',
        'property_id'
    ];

    public function building()
    {
        return $this->belongsTo(DevelopmentBuilding::class, 'development_building_id');
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }
}
""")

write_file(models_dir + 'PaymentPlan.php', """<?php
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
""")

write_file(models_dir + 'PaymentPlanInstallment.php', """<?php
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
""")

print("Models updated.")
