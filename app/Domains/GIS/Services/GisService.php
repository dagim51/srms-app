<?php

namespace App\Domains\GIS\Services;

use App\Domains\Shared\Services\DomainService;

class GisService extends DomainService
{
    public static function domain(): string
    {
        return 'GIS';
    }
}
