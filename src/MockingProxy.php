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
        $serializedMock = $this->browser->executeJavascriptRequest(
            'POST',
            '/_dusk-mocking/serialize',
            [ 'facade' => $this->facade ]
        );

        return is_null($serializedMock)
            ? null
            : Mocking::unserialize($serializedMock);
    }
}
