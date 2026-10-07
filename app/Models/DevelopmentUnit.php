<?php
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
