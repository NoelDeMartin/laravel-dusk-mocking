<?php

namespace NoelDeMartin\LaravelDusk;

use Symfony\Component\HttpFoundation\Response;

abstract class Driver
{
    /**
     * The array of active facade mocks.
     *
     * @var array
     */
    protected $mocks = [];

    /**
     * Start mocking facades.
     *
     * @return void
     */
    public function start()
    {
        $this->loadMocks();

        foreach ($this->mocks as $facade => $mock) {
            $facade::swap($mock);
        }
    }

    /**
     * Save facades state.
     *
     * @param  \Symfony\Component\HttpFoundation\Response   $response
     * @return void
     */
    public function save(Response $response)
    {
        $this->persistMocks($response);
    }

    /**
     * Replace facade instance with a mock.
     *
     * @param  string   $facade
     * @param  mixed[]  ...$arguments
     * @return void
     */
    public function mock(string $facade, ...$arguments)
    {
        if (!$this->has($facade)) {
            $mocks = $this->createMock($facade, ...$arguments);
            $facade::swap($mocks);
            $this->mocks[$facade] = $mocks;
        }
    }

    /**
     * Determine if a facade is being mocked.
     *
     * @param  string   $facade
     * @return boolean
     */
    public function has(string $facade)
    {
        return isset($this->mocks[$facade]);
    }

    /**
     * Serialize a facade mock.
     *
     * @param  string   $facade
     * @return string
     */
    public function serialize(string $facade)
    {
        return serialize($this->mocks[$facade]);
    }

    /**
     * Unserialize a facade mock.
     *
     * @param  string   $serializedMock
     * @return mixed
     */
    public function unserialize(string $serializedMock)
    {
        return unserialize($serializedMock);
    }

    /**
    * Create a facade mock.
    *
    * @param $facade   string
    * @param  mixed[]  ...$arguments
    * @return mixed
    */
    protected function createMock(string $facade, ...$arguments)
    {
        $facade::fake(...$arguments);

        return $facade::getFacadeRoot();
    }

    /**
     * Load mocks from storage.
     *
     * @return void
     */
    protected abstract function loadMocks();

    /**
     * Persists mocks.
     *
     * @param  \Symfony\Component\HttpFoundation\Response   $response
     * @return void
     */
    protected abstract function persistMocks(Response $response);
}
