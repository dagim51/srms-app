<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class RentalApplication extends Model
{
    use HasUuids;

    protected $fillable = [
        'property_id',
        'tenant_profile_id',
        'status',
        'proposed_rent',
        'message'
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function tenantProfile()
    {
        return $this->belongsTo(TenantProfile::class);
    }
}
