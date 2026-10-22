<?php

namespace App\Domains\Identity\Exceptions;

use App\Domains\Shared\Exceptions\DomainException;

class IdentityException extends DomainException
{
    public static function domain(): string
    {
        return 'Identity';
    }
}
