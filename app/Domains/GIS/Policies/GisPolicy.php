<?php

namespace App\Domains\GIS\Policies;

use App\Domains\Shared\Policies\DomainPolicy;

class GisPolicy extends DomainPolicy
{
    public static function domain(): string
    {
        return 'GIS';
    }
}
