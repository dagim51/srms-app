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
        'budget',
        'start_date',
        'end_date',
        'status',
    ];

    public function leads()
    {
        return $this->hasMany(Lead::class);
    }
}
