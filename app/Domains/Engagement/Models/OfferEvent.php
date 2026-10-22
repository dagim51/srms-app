<?php

namespace App\Domains\Engagement\Models;

use App\Models\Offer;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class OfferEvent extends Model
{
    use HasUuids;

    protected $fillable = [
        'offer_id',
        'actor_id',
        'event_type',
        'amount',
        'currency',
        'terms',
        'notes',
    ];

    protected $casts = [
        'amount' => 'float',
        'terms' => 'array',
    ];

    public function offer()
    {
        return $this->belongsTo(Offer::class);
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
