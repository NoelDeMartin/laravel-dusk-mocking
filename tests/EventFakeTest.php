<?php

namespace Testing;

use Mockery;

use Illuminate\Events\Dispatcher;

use NoelDeMartin\LaravelDusk\Fakes\EventFake;

use Testing\Stubs\StubEvent;

class EventFakeTest extends TestCase
{
    private $dispatcherMock;

    public function setUp(): void
    {
        parent::setUp();

        $this->dispatcherMock = Mockery::mock(Dispatcher::class);
        $this->dispatcherMock->closure = function() {};

        $appMock = $this->prepareFacadeMock('app');
        $appMock->shouldReceive('make')->andReturn($this->dispatcherMock);
    }

    public function test_serializes()
    {
        $eventFake = new EventFake($this->dispatcherMock);
        $eventFake->dispatch(new StubEvent);

        $eventFake = unserialize(serialize($eventFake));

        $eventFake->assertDispatched(StubEvent::class);
    }
}
