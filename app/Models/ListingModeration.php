<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ListingModeration extends Model
{
    use HasUuids;
    protected $guarded = [];

    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }
    public function moderator()
    {
        return $this->belongsTo(User::class, 'moderator_id');
    }
}
