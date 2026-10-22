<?php

namespace App\Domains\Identity\Policies;

use App\Domains\Shared\Policies\DomainPolicy;

class IdentityPolicy extends DomainPolicy
{
    public static function domain(): string
    {
        return 'Identity';
    }
}
