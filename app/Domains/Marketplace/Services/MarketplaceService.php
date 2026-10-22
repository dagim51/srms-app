<?php

namespace App\Domains\Marketplace\Services;

use App\Domains\Shared\Services\DomainService;

class MarketplaceService extends DomainService
{
    public static function domain(): string
    {
        return 'Marketplace';
    }
}
