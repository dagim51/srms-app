<?php

namespace App\Domains\Property\Services;

use App\Domains\Shared\Services\DomainService;

class PropertyService extends DomainService
{
    public static function domain(): string
    {
        return 'Property';
    }
}
