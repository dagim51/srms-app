<?php

namespace App\Domains\Organization\Policies;

use App\Domains\Shared\Policies\DomainPolicy;

class OrganizationPolicy extends DomainPolicy
{
    public static function domain(): string
    {
        return 'Organization';
    }
}
