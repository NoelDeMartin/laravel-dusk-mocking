<?php

namespace Testing;

use Mockery;

use Symfony\Component\HttpFoundation\Response;

use Illuminate\Support\Facades\Session;

use NoelDeMartin\LaravelDusk\Facades\Mocking;

class SessionDriverTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->setMockingDriver('session');
    }

    public function test_load()
    {
        Session::shouldReceive('get')
            ->once()
            ->andReturnUsing(function ($key, $default) {
                return $default;
            });

        Mocking::start();
    }

    public function test_persist()
    {
        $response = Mockery::mock(Response::class);
        Session::shouldReceive('put')->once();

        Mocking::save($response);
    }
}
