<?php

namespace App\Domains\GIS\Exceptions;

use App\Domains\Shared\Exceptions\DomainException;

class GisException extends DomainException
{
    public static function domain(): string
    {
        return 'GIS';
    }
}
