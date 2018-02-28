<?php

namespace NoelDeMartin\LaravelDusk;

use Laravel\Dusk\Browser as DuskBrowser;
use NoelDeMartin\LaravelDusk\Facades\Mocking;

class Browser extends DuskBrowser
{
    /**
     * Mock a facade and return the mock proxy.
     *
     * @param  string   $facade
     * @param  mixed[]  ...$arguments
     * @return \NoelDeMartin\LaravelDusk\MockingProxy
     */
    public function mock(string $facade, ...$arguments)
    {
        // Spread statically registered fakes to server
        if (Mocking::hasFake($facade)) {
            $this->registerFake($facade, Mocking::getFake($facade));
        }

        $this->visit(
            '/_dusk-mocking/mock/'.
            '?facade='.urlencode($facade).
            '&arguments='.urlencode(json_encode($arguments))
        );

        return new MockingProxy($this, $facade);
    }

    /**
     * Alias for mock.
     *
     * @param  string   $facade
     * @param  mixed[]  ...$arguments
     * @return \NoelDeMartin\LaravelDusk\MockingProxy
     */
    public function fake(string $facade, ...$arguments)
    {
        return $this->mock($facade, ...$arguments);
    }

    /**
     * Register fake class.
     *
     * @param  string   $facade
     * @param  string   $fake
     * @return NoelDeMartin\LaravelDusk\Browser
     */
    public function registerFake(string $facade, $fake)
    {
        $this->visit(
            '/_dusk-mocking/register/'.
            '?facade='.urlencode($facade).
            '&fake='.urlencode($fake)
        );

        return $this;
    }
}
