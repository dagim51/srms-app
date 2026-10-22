<?php

namespace App\Domains\Shared\Services;

abstract class DomainService
{
    /**
     * The domain name this service belongs to.
     */
    abstract public static function domain(): string;

    /**
     * Boot the service (called on first resolution).
     */
    public function boot(): void
    {
        //
    }

    /**
     * Get the domain name.
     */
    public function getDomain(): string
    {
        return static::domain();
    }
}
