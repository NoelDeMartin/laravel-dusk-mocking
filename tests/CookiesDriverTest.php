<?php

namespace Testing;

use Mockery;

use Symfony\Component\HttpFoundation\Response;

use Illuminate\Support\Facades\Cookie;

use NoelDeMartin\LaravelDusk\Facades\Mocking;

class CookiesDriverTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->setMockingDriver('cookies');
    }

    public function test_load()
    {
        $this->prepareFacadeMock('request')
            ->shouldReceive('cookie')
            ->once()
            ->andReturnUsing(function ($key, $default) {
                return $default;
            });

        Mocking::start();
    }

    public function test_persist()
    {
        $response = Mockery::mock(Response::class);
        $headers = Mockery::mock(StdClass::class);
        $headers->shouldReceive('setCookie')->once();
        $response->headers = $headers;
        Cookie::shouldReceive('forever')->once();

        Mocking::save($response);
    }
}
