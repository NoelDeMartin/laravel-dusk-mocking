<?php

namespace NoelDeMartin\LaravelDusk;

use Illuminate\Support\Manager;
use NoelDeMartin\LaravelDusk\Drivers\SessionDriver;
use NoelDeMartin\LaravelDusk\Drivers\CookiesDriver;

class MockingManager extends Manager
{
    /**
     * Get the default mocking driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        $config = $this->app['config'];

        return $config->has('dusk-mocking')
            ? $config['dusk-mocking.driver']
            : 'cookies';
    }

    /**
     * Create an instance of the Cookies mocking driver.
     *
     * @return \NoelDeMartin\LaravelDusk\MockingDriver
     */
    protected function createCookiesDriver()
    {
        return new CookiesDriver;
    }

    /**
     * Create an instance of the Session mocking driver.
     *
     * @return \NoelDeMartin\LaravelDusk\SessionDriver
     */
    protected function createSessionDriver()
    {
        return new SessionDriver;
    }
}
