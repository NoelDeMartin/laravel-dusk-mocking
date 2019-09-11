<?php

namespace Testing;

use Mockery;

use Faker\Factory as Faker;

use PHPUnit\Framework\TestCase as BaseTestCase;

use Illuminate\Container\Container;
use Illuminate\Support\Facades\Facade;

use NoelDeMartin\LaravelDusk\Facades\Mocking;
use NoelDeMartin\LaravelDusk\MockingManager;

class TestCase extends BaseTestCase
{
    public function setUp(): void
    {
        $this->faker = Faker::create();
        $this->app = Mockery::mock(Container::class);
        $this->facades = [];
        $this->app->shouldReceive('instance');
        $this->app
            ->shouldReceive('offsetGet')
            ->andReturnUsing(function($key) {
                if (isset($this->facades[$key])) {
                    return $this->facades[$key];
                }
            });
        $this->app
            ->shouldReceive('make')
            ->andReturnUsing(function($abstract) {
                return $abstract;
            });
        $this->setMockingDriver();
        Container::setInstance($this->app);
        Facade::setFacadeApplication($this->app);
    }

    public function tearDown(): void
    {
        Mockery::close();
        Facade::clearResolvedInstances();
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
