<?php

namespace App\Domains\Listing\Models;

use App\Models\Listing;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Promotion extends Model
{
    use HasUuids;

    protected $table = 'promotions';

    protected $fillable = [
        'listing_id',
        'tier',
        'start_date',
        'end_date',
        'amount_paid',
        'status',
        'impressions_count',
        'clicks_count',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'amount_paid' => 'decimal:2',
        'impressions_count' => 'integer',
        'clicks_count' => 'integer',
    ];

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isActive(): bool
    {
        return $this->status === 'active' &&
            $this->start_date <= now() &&
            $this->end_date >= now();
    }
}
