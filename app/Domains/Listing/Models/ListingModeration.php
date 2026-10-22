<?php

namespace App\Domains\Listing\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

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
