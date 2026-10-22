<?php

namespace App\Domains\Marketplace\Policies;

use App\Domains\Shared\Policies\DomainPolicy;

class MarketplacePolicy extends DomainPolicy
{
    public static function domain(): string
    {
        return 'Marketplace';
    }
}
