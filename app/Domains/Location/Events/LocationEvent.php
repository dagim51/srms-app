<?php

namespace App\Domains\Location\Events;

use App\Domains\Shared\Events\DomainEvent;

abstract class LocationEvent extends DomainEvent
{
    public static function domain(): string
    {
        return 'Location';
    }
}
