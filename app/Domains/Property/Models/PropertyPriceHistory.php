<?php

namespace App\Domains\Property\Models;

use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PropertyPriceHistory extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $table = 'property_price_history';

    protected $fillable = [
        'property_id',
        'price',
        'currency',
        'changed_by',
        'reason',
        'created_at',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function changer()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
