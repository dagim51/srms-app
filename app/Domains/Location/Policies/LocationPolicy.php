<?php

namespace App\Domains\Location\Policies;

use App\Domains\Shared\Policies\DomainPolicy;

class LocationPolicy extends DomainPolicy
{
    public static function domain(): string
    {
        return 'Location';
    }
}
