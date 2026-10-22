<?php

namespace App\Domains\Listing\Exceptions;

use App\Domains\Shared\Exceptions\DomainException;

class ListingException extends DomainException
{
    public static function domain(): string
    {
        return 'Listing';
    }
}
