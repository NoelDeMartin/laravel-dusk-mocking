<?php

namespace NoelDeMartin\LaravelDusk\Http\Controllers;

use NoelDeMartin\LaravelDusk\Facades\Mocking;

class MockingController
{
    /**
     * Setup a facade mock.
     *
     * @return void
     */
    public function mock()
    {
        $facade = request('facade');
        $arguments = json_decode(request('arguments'));
        Mocking::mock($facade, ...$arguments);
    }

    /**
     * Register facade fake.
     *
     * @return void
     */
    public function register()
    {
        $facade = request('facade');
        $fake = request('fake');
        Mocking::registerFake($facade, $fake);
    }

    /**
     * Retrieve serialized facade mock.
     *
     * @return mixed
     */
    public function serialize()
    {
        $facade = request('facade');
        if (Mocking::has($facade)) {
            return response()->json(Mocking::serialize($facade));
        }
    }
}
