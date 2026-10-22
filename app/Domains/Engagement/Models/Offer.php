<?php

namespace App\Domains\Engagement\Models;

use App\Models\Listing;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function parentOffer(): BelongsTo
    {
        return $this->belongsTo(Offer::class, 'parent_offer_id');
    }

    public function counterOffers(): HasMany
    {
        return $this->hasMany(Offer::class, 'parent_offer_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(OfferEvent::class);
    }
}
