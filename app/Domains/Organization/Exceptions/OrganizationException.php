<?php

namespace App\Domains\Organization\Exceptions;

use App\Domains\Shared\Exceptions\DomainException;

class OrganizationException extends DomainException
{
    public static function domain(): string
    {
        return 'Organization';
    }
}
