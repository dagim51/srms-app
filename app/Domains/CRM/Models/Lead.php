<?php

namespace App\Domains\CRM\Models;

use App\Models\CrmActivity;
use App\Models\Listing;
use App\Models\MarketingCampaign;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'assigned_to',
        'name',
        'email',
        'phone',
        'source',
        'status',
        'marketing_campaign_id',
        'listing_id',
        'notes',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }

    public function activities()
    {
        return $this->hasMany(CrmActivity::class);
    }

    public function campaign()
    {
        return $this->belongsTo(MarketingCampaign::class, 'marketing_campaign_id');
    }
}
