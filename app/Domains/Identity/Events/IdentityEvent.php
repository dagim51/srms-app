<?php

namespace App\Domains\Identity\Events;

use App\Domains\Shared\Events\DomainEvent;

abstract class IdentityEvent extends DomainEvent
{
    public static function domain(): string
    {
        return 'Identity';
    }
}
