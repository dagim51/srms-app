<?php

namespace App\Domains\Listing\Events;

use App\Domains\Shared\Events\DomainEvent;

abstract class ListingEvent extends DomainEvent
{
    public static function domain(): string
    {
        return 'Listing';
    }
}
