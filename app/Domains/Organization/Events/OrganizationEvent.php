<?php

namespace App\Domains\Organization\Events;

use App\Domains\Shared\Events\DomainEvent;

abstract class OrganizationEvent extends DomainEvent
{
    public static function domain(): string
    {
        return 'Organization';
    }
}
