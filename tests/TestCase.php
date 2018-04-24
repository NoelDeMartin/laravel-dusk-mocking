<?php

use Faker\Factory as Faker;
use Illuminate\Support\Facades\Facade;
use NoelDeMartin\LaravelDusk\MockingManager;
use NoelDeMartin\LaravelDusk\Facades\Mocking;
use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    public function setUp()
    {
        $this->faker = Faker::create();
        $this->app = Mockery::mock(ArrayAccess::class);
        $this->facades = [];
        $this->app->shouldReceive('instance');
        $this->app
            ->shouldReceive('offsetGet')
            ->andReturnUsing(function($key) {
                if (isset($this->facades[$key])) {
                    return $this->facades[$key];
                }
            });
        $this->setMockingDriver();
        Facade::setFacadeApplication($this->app);
    }

    public function tearDown()
    {
        Mockery::close();
    }

    protected function prepareFacadeMock($name)
    {
        if (!isset($this->facades[$name])) {
            $this->facades[$name] = Mockery::mock(StdClass::class);
        }

        return $this->facades[$name];
    }

    protected function setMockingDriver($driver = 'cookies')
    {
        Mocking::swap((new MockingManager($this->app))->driver($driver));
    }
}
