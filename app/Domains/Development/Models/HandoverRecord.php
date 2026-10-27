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
}
