<?php

namespace NoelDeMartin\LaravelDusk;

use Laravel\Dusk\Browser as DuskBrowser;

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
        $this->visit(
            '/_dusk-mocking/mock/'.
            '?facade='.urlencode($facade).
            '&arguments='.urlencode(json_encode($arguments))
        );

        return new MockingProxy($this, $facade);
    }
}
