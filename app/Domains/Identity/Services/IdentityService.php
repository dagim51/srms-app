<?php

namespace App\Domains\Identity\Services;

use App\Domains\Shared\Services\DomainService;

class IdentityService extends DomainService
{
    public static function domain(): string
    {
        return 'Identity';
    }
}
