<?php

namespace App\Domains\Transaction\Models;

use App\Models\Document;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentVersion extends Model
{
    use HasUuids;

    protected $fillable = [
        'document_id',
        'version_number',
        'file_path',
        'checksum_sha256',
        'uploaded_by',
        'change_summary',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
