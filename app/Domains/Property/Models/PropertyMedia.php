<?php

namespace App\Domains\Property\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class PropertyMedia extends Model
{
    use HasUuids, SoftDeletes;

    protected $guarded = [];

    protected $appends = ['url', 'is_primary'];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function getUrlAttribute(): string
    {
        if (! $this->storage_key) {
            return '';
        }
        if (str_starts_with($this->storage_key, 'http://') || str_starts_with($this->storage_key, 'https://')) {
            return $this->storage_key;
        }

        return Storage::disk('public')->url($this->storage_key);
    }

    public function getIsPrimaryAttribute(): bool
    {
        return $this->sort_order === 0;
    }
}
