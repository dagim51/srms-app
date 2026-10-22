<?php

namespace App\Domains\Listing\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ListingFlag extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'listing_id',
        'reporter_id',
        'assigned_moderator_id',
        'reason',
        'description',
        'severity',
        'status',
        'moderator_notes',
        'reported_at',
        'reviewed_at',
        'resolved_at',
    ];

    protected $casts = [
        'reported_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function assignedModerator()
    {
        return $this->belongsTo(User::class, 'assigned_moderator_id');
    }
}
