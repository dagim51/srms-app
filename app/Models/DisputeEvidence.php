<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class DisputeEvidence extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'dispute_id',
        'submitted_by',
        'type',
        'title',
        'description',
        'file_path',
        'file_url',
        'mime_type',
        'file_size',
        'metadata',
        'is_verified',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'metadata' => 'array',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
    ];

    public function dispute()
    {
        return $this->belongsTo(Dispute::class);
    }

    public function submittedBy()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}