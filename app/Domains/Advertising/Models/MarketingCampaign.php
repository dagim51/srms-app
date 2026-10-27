<?php

namespace App\Domains\Advertising\Models;

use App\Domains\CRM\Models\Lead;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class MarketingCampaign extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'channel',
        'placement',
        'headline',
        'subheadline',
        'banner_image_url',
        'target_url',
        'cta_text',
        'impressions_count',
        'clicks_count',
        'budget',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'impressions_count' => 'integer',
        'clicks_count' => 'integer',
        'budget' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function recordImpression(): void
    {
        $this->increment('impressions_count');
    }

    public function recordClick(): void
    {
        $this->increment('clicks_count');
    }

    public function leads()
    {
        return $this->hasMany(Lead::class);
    }
}
