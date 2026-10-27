<?php

namespace App\Domains\Subscription\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    protected $table = 'subscription_plans';

    protected $fillable = [
        'name',
        'slug',
        'price',
        'billing_cycle',
        'max_listings',
        'featured_listings_allowed',
        'analytics_access',
        'is_active',
    ];

    protected $casts = [
        'price' => 'float',
        'max_listings' => 'integer',
        'featured_listings_allowed' => 'integer',
        'analytics_access' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }
}
