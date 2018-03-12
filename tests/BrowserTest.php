<?php

use NoelDeMartin\LaravelDusk\Browser;

class BrowserTest extends TestCase
{
    public function test_fake()
    {
        $facade = $this->faker->word;
        $args = $this->faker->words();
        $driver = Mockery::mock(StdClass::class);
        $driver
            ->shouldReceive('navigate->to')
            ->with(
                'http://laravel.dev/_dusk-mocking/mock/'.
                '?facade='.urlencode($facade).
                '&arguments='.urlencode(json_encode($args))
            )
            ->twice();
        Browser::$baseUrl = 'http://laravel.dev';
        $browser = new Browser($driver);

        $browser->fake($facade, ...$args);
        $browser->mock($facade, ...$args);
    }

    public function test_register()
    {
        $facade = $this->faker->word;
        $fake = $this->faker->word;
        $driver = Mockery::mock(StdClass::class);
        $driver
            ->shouldReceive('navigate->to')
            ->with(
                'http://laravel.dev/_dusk-mocking/register/'.
                '?facade='.urlencode($facade).
                '&fake='.urlencode($fake)
            )
            ->once();
        Browser::$baseUrl = 'http://laravel.dev';
        $browser = new Browser($driver);

        $browser->registerFake($facade, $fake);
    }
}
