<?php

namespace App\Domains\Shared\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

abstract class DomainEvent
{
    use Dispatchable, SerializesModels;

    /**
     * The domain this event belongs to.
     */
    abstract public static function domain(): string;

    /**
     * Get the event name for logging/debugging.
     */
    public function getEventName(): string
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
