<?php

namespace App\Domains\Marketplace\Exceptions;

use App\Domains\Shared\Exceptions\DomainException;

class MarketplaceException extends DomainException
{
    public static function domain(): string
    {
        return 'Marketplace';
    }
}
