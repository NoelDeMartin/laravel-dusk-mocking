<?php

namespace NoelDeMartin\LaravelDusk\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \NoelDeMartin\LaravelDusk\MockingManager
 * @see \NoelDeMartin\LaravelDusk\Driver
 */
class Mocking extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'dusk-mocking';
    }
}
