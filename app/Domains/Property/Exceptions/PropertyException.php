<?php

namespace App\Domains\Property\Exceptions;

use App\Domains\Shared\Exceptions\DomainException;

class PropertyException extends DomainException
{
    public static function domain(): string
    {
        return 'Property';
    }
}
