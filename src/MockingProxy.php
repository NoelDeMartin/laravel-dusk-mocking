<?php

namespace NoelDeMartin\LaravelDusk;

use Exception;
use Laravel\Dusk\Browser;
use NoelDeMartin\LaravelDusk\Facades\Mocking;

class MockingProxy
{
    /**
     * Browser where facades are being mocked.
     *
     * @var Browser
     */
    private $browser;

    /**
     * The facade being mocked.
     *
     * @var string
     */
    private $facade;

    /**
     * Create a new facade mocking proxy.
     *
     * @param  Browser  $browser
     * @param  string   $facade
     * @return void
     */
    public function __construct(Browser $browser, string $facade)
    {
        $this->browser = $browser;
        $this->facade = $facade;
    }

    /**
     * Dynamically pass method calls to the browser mocking the facade.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (! is_null($mock = $this->getFacadeMock())) {
            return $mock->{$method}(...$parameters);
        } else {
            throw new Exception(
                'Unable to retrieve mock for ['.$this->facade.'].'
            );
        }
    }

    /**
     * Get mock instance of the facade.
     *
     * @return mixed
     */
    protected function getFacadeMock()
    {
        $this->browser->driver->manage()->timeouts()->setScriptTimeout(1);

        $serializedMock = $this->browser->driver->executeAsyncScript(
            'var callback = arguments[0];'.
            'var request = new XMLHttpRequest();'.
            'request.open("GET", "/_dusk-mocking/serialize?facade='.urlencode($this->facade).'", true);'.
            'request.withCredentials = true;'.
            'request.onreadystatechange = function() {'.
                'if (request.readyState == XMLHttpRequest.DONE) callback(JSON.parse(request.responseText));'.
            '};'.
            'request.send();'
        );

        return is_null($serializedMock)
            ? null
            : Mocking::unserialize($serializedMock);
    }
}
