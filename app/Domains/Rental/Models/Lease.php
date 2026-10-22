<?php

namespace App\Domains\Rental\Models;

use App\Models\Property;
use App\Models\RentSchedule;
use App\Models\TenantProfile;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Lease extends Model
{
    use HasUuids;

    protected $fillable = [
        'property_id',
        'tenant_profile_id',
        'start_date',
        'end_date',
        'rent_amount',
        'deposit_amount',
        'status',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function tenantProfile()
    {
        return $this->belongsTo(TenantProfile::class);
    }

    public function schedules()
    {
        return $this->hasMany(RentSchedule::class);
    }
}
