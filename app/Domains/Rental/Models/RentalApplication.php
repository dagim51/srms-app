<?php

namespace App\Domains\Rental\Models;

use App\Models\Property;
use App\Models\TenantProfile;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class RentalApplication extends Model
{
    use HasUuids;

    protected $fillable = [
        'property_id',
        'tenant_profile_id',
        'status',
        'proposed_rent',
        'message',
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
