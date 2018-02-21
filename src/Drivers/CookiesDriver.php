<?php

namespace NoelDeMartin\LaravelDusk\Drivers;

use NoelDeMartin\LaravelDusk\Driver;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

class CookiesDriver extends Driver
{
    /**
     * Name of the cookie used to serialize mocks.
     *
     * @var string
     */
    const COOKIE_NAME = 'Dusk-Mocking';

    /**
     * Load mocks from storage.
     *
     * @return void
     */
    protected function loadMocks()
    {
        $serializedMocks = Cookie::get(self::COOKIE_NAME, '{}');
        $serializedMocks = json_decode($serializedMocks, true);

        foreach ($serializedMocks as $facade => $serializedMock) {
            $this->mocks[$facade] = $this->unserialize($serializedMock);
        }
    }

    /**
     * Persist mocks.
     *
     * @param  \Symfony\Component\HttpFoundation\Response   $response
     * @return void
     */
    protected function persistMocks(Response $response)
    {
        $serializedMocks = [];
        foreach (array_keys($this->mocks) as $facade) {
            $serializedMocks[$facade] = $this->serialize($facade);
        }

        $response->headers->setCookie(
            Cookie::forever(
                static::COOKIE_NAME,
                json_encode($serializedMocks),
                '/'
            )
        );
    }
}
