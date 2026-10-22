<?php

namespace App\Domains\Marketplace\Events;

use App\Domains\Shared\Events\DomainEvent;

abstract class MarketplaceEvent extends DomainEvent
{
    public static function domain(): string
    {
        return 'Marketplace';
    }
}
