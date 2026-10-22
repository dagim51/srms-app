<?php

namespace App\Actions;

use Closure;

abstract class Action
{
    /**
     * Execute with transaction wrapper.
     */
    protected function transactional(Closure $callback): mixed
    {
        return \DB::transaction($callback);
    }

    /**
     * Dispatch domain event after successful execution.
     */
    protected function dispatchEvent(object $event): void
    {
        event($event);
    }
}
