<?php

namespace App\Domains\GIS\Events;

use App\Domains\Shared\Events\DomainEvent;

abstract class GisEvent extends DomainEvent
{
    public static function domain(): string
    {
        return 'GIS';
    }
}
