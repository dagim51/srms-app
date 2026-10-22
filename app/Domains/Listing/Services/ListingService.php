<?php

namespace App\Domains\Listing\Services;

use App\Domains\Shared\Services\DomainService;

class ListingService extends DomainService
{
    public static function domain(): string
    {
        return 'Listing';
    }
}
