<?php

namespace App\Domains\Stay\Models;

use App\Models\Accommodation;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExternalCalendarSync extends Model
{
    use HasUuids;

    protected $fillable = [
        'accommodation_id',
        'name',
        'url',
        'sync_interval_minutes',
        'last_synced_at',
        'status',
        'last_error',
        'events_count',
    ];

    protected $casts = [
        'last_synced_at' => 'datetime',
        'sync_interval_minutes' => 'integer',
        'events_count' => 'integer',
    ];

    public function accommodation(): BelongsTo
    {
        return $this->belongsTo(Accommodation::class);
    }

    public function getFeedUrlAttribute(): ?string
    {
        return $this->url;
    }

    public function setFeedUrlAttribute(?string $value): void
    {
        $this->attributes['url'] = $value;
    }

    public function getSyncStatusAttribute(): string
    {
        return $this->status ?? 'active';
    }

    public function setSyncStatusAttribute(?string $value): void
    {
        $this->attributes['status'] = $value ?? 'active';
    }

    public function getEventsSyncedAttribute(): int
    {
        return $this->events_count ?? 0;
    }

    public function setEventsSyncedAttribute(?int $value): void
    {
        $this->attributes['events_count'] = $value ?? 0;
    }

    public function getProviderNameAttribute(): string
    {
        $url = strtolower($this->url ?? '');
        $name = strtolower($this->name ?? '');

        if (str_contains($url, 'airbnb') || str_contains($name, 'airbnb')) {
            return 'airbnb';
        }
        if (str_contains($url, 'google') || str_contains($name, 'google')) {
            return 'google';
        }
        if (str_contains($url, 'vrbo') || str_contains($name, 'vrbo') || str_contains($url, 'homeaway')) {
            return 'vrbo';
        }
        if (str_contains($url, 'booking.com') || str_contains($name, 'booking')) {
            return 'booking';
        }
        if (str_contains($url, 'apple') || str_contains($url, 'icloud')) {
            return 'apple';
        }

        return 'ical';
    }
}
