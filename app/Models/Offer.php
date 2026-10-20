<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class Offer extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'listing_id',
        'buyer_id',
        'parent_offer_id',
        'amount',
        'status',
        'expires_at',
        'notes',
    ];

    protected $casts = [
        'amount' => 'float',
        'expires_at' => 'datetime',
    ];

    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function parentOffer()
    {
        return $this->belongsTo(Offer::class, 'parent_offer_id');
    }

    public function counterOffers()
    {
        return $this->hasMany(Offer::class, 'parent_offer_id');
    }

    public function events()
    {
        return $this->hasMany(OfferEvent::class);
    }
}
