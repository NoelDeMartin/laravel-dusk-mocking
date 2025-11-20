<?php

namespace NoelDeMartin\LaravelDusk\Fakes;

use Illuminate\Http\Client\Factory;

class HttpFake extends Factory
{
    protected $config = [];

    public function __construct(mixed $config = null)
    {
        $this->config = json_decode(json_encode($config), true);

        parent::__construct(null);

        if (! empty($this->config)) {
            parent::fake($this->config);
        }
    }

    public function __sleep() : array
    {
        return [
            'recording',
            'recorded',
            'preventStrayRequests',
            'allowedStrayRequestUrls',
            'config',
        ];
    }

    public function __wakeup() : void
    {
        $this->stubCallbacks = collect();

        if (! empty($this->config)) {
            parent::fake($this->config);
        }
    }
}