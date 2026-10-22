<?php

namespace App\Domains\Shared\Exceptions;

use Exception;

abstract class DomainException extends Exception
{
    /**
     * The domain this exception belongs to.
     */
    abstract public static function domain(): string;

    /**
     * The error code for API responses.
     */
    public function getErrorCode(): string
    {
        return static::class;
    }

    /**
     * Get the domain name.
     */
    public function getDomain(): string
    {
        return static::domain();
    }
}
