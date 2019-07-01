<?php

namespace NoelDeMartin\LaravelDusk\Fakes;

use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Testing\Fakes\EventFake as BaseEventFake;

class EventFake extends BaseEventFake
{
    /**
     * Prepare object for serialization.
     *
     * @return array
     */
    public function __sleep()
    {
        $attributes = array_keys(get_object_vars($this));

        $index = array_search('dispatcher', $attributes);

        if ($index !== false) {
            array_splice($attributes, $index, 1);
        }

        return $attributes;
    }

    /**
     * Restore object after deserialization.
     *
     * @return void
     */
    public function __wakeup()
    {
        $this->dispatcher = App::make(Dispatcher::class);
    }
}
