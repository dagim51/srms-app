<?php

namespace App\Domains\Development\Models;

use App\Models\DevelopmentUnit;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HandoverRecord extends Model
{
    use HasUuids;

    protected $table = 'handover_records';

    protected $fillable = [
        'unit_id',
        'buyer_id',
        'status',
        'keys_released_at',
        'inspection_checklist',
        'signed_document_url',
    ];

    protected $casts = [
        'keys_released_at' => 'datetime',
        'inspection_checklist' => 'array',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(DevelopmentUnit::class, 'unit_id');
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function snaggingItems()
    {
        return $this->hasMany(SnaggingItem::class, 'handover_record_id');
    }

    /**
     * Check if any snagging defects are still pending rectification.
     */
    public function hasPendingSnags(): bool
    {
        return $this->snaggingItems()->whereIn('status', ['open', 'rectified'])->exists();
    }

    /**
     * Check if handover is eligible for final sign-off.
     */
    public function canCompleteHandover(): bool
    {
        return ! $this->hasPendingSnags();
    }

    /**
     * Finalize handover, record keys release, and issue certificate.
     */
    public function completeHandover(?User $signer = null, array $checklist = []): void
    {
        if (! $this->canCompleteHandover()) {
            throw new \DomainException('Cannot complete handover while unresolved defect snagging items remain.');
        }

        $this->update([
            'status' => 'completed',
            'keys_released_at' => now(),
            'signed_document_url' => 'certificates/handover-cert-'.$this->id.'.pdf',
            'inspection_checklist' => array_merge($this->inspection_checklist ?? [], $checklist, [
                'signed_by' => $signer ? $signer->name : 'Buyer',
                'signed_at' => now()->toISOString(),
            ]),
        ]);
    }
}
