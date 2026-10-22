<?php

namespace App\Domains\Engagement\Models;

use App\Models\Listing;
use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inquiry extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'listing_id',
        'property_id',
        'requester_id',
        'seller_agent_id',
        'status',
        'message',
    ];

    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function sellerAgent()
    {
        return $this->belongsTo(User::class, 'seller_agent_id');
    }
}
