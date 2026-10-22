<?php

namespace App\Domains\Location\Services;

use App\Domains\Shared\Services\DomainService;

class LocationService extends DomainService
{
    public static function domain(): string
    {
        return 'Location';
    }
}
