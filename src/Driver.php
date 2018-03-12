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
     * The array of fake facade classes.
     *
     * @var array
     */
    protected $fakes = [];

    /**
     * Start mocking facades.
     *
     * @return void
     */
    public function start()
    {
        $this->load();

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
        $this->persist($response);
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
        if (! $this->has($facade)) {
            $mocks = $this->createMock($facade, ...$arguments);
            $facade::swap($mocks);
            $this->mocks[$facade] = $mocks;
        }
    }

    /**
     * Register new facade fake.
     *
     * @param  string   $facade
     * @param  string   $fake
     * @return void
     */
    public function registerFake(string $facade, string $fake)
    {
        $this->fakes[$facade] = $fake;
    }

    /**
     * Determine if a facade is being mocked.
     *
     * @param  string   $facade
     * @return bool
     */
    public function has(string $facade)
    {
        return isset($this->mocks[$facade]);
    }

    /**
     * Determine if a facade fake is registered.
     *
     * @param  string   $facade
     * @return bool
     */
    public function hasFake(string $facade)
    {
        return isset($this->fakes[$facade]);
    }

    /**
     * Get registered fake.
     *
     * @param  string   $facade
     * @return string | null
     */
    public function getFake(string $facade)
    {
        return $this->hasFake($facade) ? $this->fakes[$facade] : null;
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
        if (isset($this->fakes[$facade])) {
            return new $this->fakes[$facade](...$arguments);
        } else {
            $facade::fake(...$arguments);

            return $facade::getFacadeRoot();
        }
    }

    /**
     * Load data from storage.
     *
     * @return void
     */
    abstract protected function load();

    /**
     * Persists data.
     *
     * @param  \Symfony\Component\HttpFoundation\Response   $response
     * @return void
     */
    abstract protected function persist(Response $response);
}
