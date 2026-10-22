<?php

namespace App\Domains\Location\Exceptions;

use App\Domains\Shared\Exceptions\DomainException;

class LocationException extends DomainException
{
    public static function domain(): string
    {
        return 'Location';
    }
}
