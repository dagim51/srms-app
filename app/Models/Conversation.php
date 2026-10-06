<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class Conversation extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'subject',
        'listing_id',
        'property_id',
        'inquiry_id',
    ];

    public function participants()
    {
        return $this->hasMany(ConversationParticipant::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function inquiry()
    {
        return $this->belongsTo(Inquiry::class);
    }
}
