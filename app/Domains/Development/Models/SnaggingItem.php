<?php

namespace App\Domains\Development\Models;

use App\Models\DevelopmentUnit;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SnaggingItem extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'snagging_items';

    protected $fillable = [
        'unit_id',
        'handover_record_id',
        'buyer_id',
        'inspector_id',
        'room_area',
        'description',
        'photo_urls',
        'severity',
        'status',
        'rectified_at',
        'verified_at',
        'notes',
    ];

    protected $casts = [
        'photo_urls' => 'array',
        'rectified_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(DevelopmentUnit::class, 'unit_id');
    }

    public function handoverRecord(): BelongsTo
    {
        return $this->belongsTo(HandoverRecord::class, 'handover_record_id');
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }

    /**
     * Mark the defect as rectified by the construction contractor.
     */
    public function markRectified(?string $notes = null): void
    {
        $this->update([
            'status' => 'rectified',
            'rectified_at' => now(),
            'notes' => $notes ?? $this->notes,
        ]);
    }

    /**
     * Verify and sign off on defect rectification by the buyer or inspector.
     */
    public function verifySignOff(User $signer, ?string $notes = null): void
    {
        $this->update([
            'status' => 'verified',
            'verified_at' => now(),
            'notes' => $notes ?? $this->notes,
        ]);
    }
}
