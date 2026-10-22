<?php

namespace App\Domains\Organization\Services;

use App\Domains\Shared\Services\DomainService;

class OrganizationService extends DomainService
{
    public static function domain(): string
    {
        return 'Organization';
    }
}
