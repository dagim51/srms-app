<?php

namespace App\Domains\Transaction\Models;

use App\Models\DocumentAccessLog;
use App\Models\DocumentVersion;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\URL;

class Document extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'name',
        'type',
        'status',
        'file_path',
        'mime_type',
        'file_size',
        'description',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(DocumentVersion::class)->orderBy('created_at', 'desc');
    }

    public function accessLogs(): HasMany
    {
        return $this->hasMany(DocumentAccessLog::class)->orderBy('created_at', 'desc');
    }

    public function getDownloadUrlAttribute(): string
    {
        return URL::temporarySignedRoute(
            'dashboard.documents.download',
            now()->addMinutes(15),
            ['document' => $this->id]
        );
    }
}
