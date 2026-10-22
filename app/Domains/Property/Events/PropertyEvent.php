<?php

namespace App\Domains\Property\Events;

use App\Domains\Shared\Events\DomainEvent;

abstract class PropertyEvent extends DomainEvent
{
    public static function domain(): string
    {
        return 'Property';
    }
}
