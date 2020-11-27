# Laravel Dusk Mocking [![Build Status](https://travis-ci.org/NoelDeMartin/laravel-dusk-mocking.svg?branch=master)](https://travis-ci.org/NoelDeMartin/laravel-dusk-mocking) [![Github Actions Status](https://github.com/noeldemartin/laravel-dusk-mocking/workflows/Testing/badge.svg)](https://github.com/noeldemartin/laravel-dusk-mocking/actions)

When running browser tests with Laravel Dusk, [it is not possible](https://github.com/laravel/dusk/issues/152) to mock facades like [it is usually done](https://laravel.com/docs/mocking) for http tests. This package aims to provide that functionality. However, it does so by doing some workarounds. It is recommended to read the [Disclaimer](#disclaimer) (and in particular the [Limitations](#limitations)) before using it.

Before adding it to your project, you can also give it a try with a bare-bones Laravel application prepared with tests running on a CI environment here: [laravel-dusk-mocking-sandbox](https://github.com/NoelDeMartin/laravel-dusk-mocking-sandbox/).

# Installation

Install using composer:

```
composer require --dev noeldemartin/laravel-dusk-mocking
```

Add the following code to your base test case (usually `DuskTestCase`).

```php
use NoelDeMartin\LaravelDusk\Browser;

...

protected function newBrowser($driver)
{
    return new Browser($driver);
}
```

# Usage

The conceptual usage is the same as can be learned on Laravel's documentation about [mocking](https://laravel.com/docs/5.6/mocking). The only difference is that in Dusk, mocking can be set up independently on each browser instance. For that reason, instead of calling static methods we will call instance methods. Look at the following example on how to mock the Mail facade:

```php
public function testOrderShipping()
{
    $this->browse(function ($browser) use ($user) {
        $mail = $browser->fake(Mail::class);

        $browser->visit('...')
                // Perform order shipping...
                ->assertSee('Order purchased! Check your email for details!');

        $mail->assertSent(OrderShipped::class, function ($mail) use ($order) {
            return $mail->order->id === $order->id;
        });

        // Assert a message was sent to the given users...
        $mail->assertSent(OrderShipped::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email) &&
                   $mail->hasCc('...') &&
                   $mail->hasBcc('...');
        });

        // Assert a mailable was sent twice...
        $mail->assertSent(OrderShipped::class, 2);

        // Assert a mailable was not sent...
        $mail->assertNotSent(AnotherMailable::class);
    });
}
```

Notice how the api is the same as Http tests.

## Configuration

Drivers serialize mocking data through requests, and cookies are used by default. The drawback is that using cookies, there is a size limit for how much information can be stored (4KB). For that reason, different drivers can be configured. Create a file named `dusk-mocking.php` inside your application config folder to change the default driver:

```php
<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Driver
    |--------------------------------------------------------------------------
    |
    | This option controls the default driver for storing mock data.
    |
    | Supported: "cookies", "session"
    |
    */
    'driver' => 'session',

];
```

### Script timeout

As explained [below](#how-does-it-work), each test may send multiple requests to the application to serialize/deserialize fakes. In order to prevent changing the state of the browser, this is done using javascript XHR requests. These requests will timeout in 1 second by default, but this can be configured using the `$javascriptRequestsTimeout` variable:

```php
protected function newBrowser($driver)
{
    Browser::$javascriptRequestsTimeout = 5;

    return new Browser($driver);
}
```

## Extending

Doing this, only the functionality analogous of calling `fake` is available, and not methods mocking. In order to mock custom facades or modify Laravel's default fakes, the system can be extended by using custom fakes. Those can be registered in two ways:

- Registered globally (every test using fakes will use them). Add the following to your base test case (usually `DuskTestCase`):

```php
public function setUp()
{
    parent::setUp();

    // Register fake mocks
    Mocking::registerFake(Mail::class, MyMailFake::class);
}
```

- Another option is to register them only for one browser. This can be useful if it's necessary to have different fakes for multiple browsers or for aesthetic reasons (performance is not affected either way):

```php
$this->browse(function (Browser $browser) {
    $browser->registerFake(Mail::class, MyMailFake::class);

    $mail = $browser->mock(Mail::class);

    // Proceed with the test
});
```

In order to understand how to implement these Fake classes, you can take a look at how [Laravel fakes](https://github.com/laravel/framework/tree/5.6/src/Illuminate/Support/Testing/Fakes) are implemented, since those are the ones used by default. This classes can also be used as a base, extending them and adding any modifications.

# Disclaimer

Most scenarios should be covered with http tests, since they both run faster and are more reliable when testing your code. The same could be said when testing your frontend and Javascript, there are multiple frameworks specific for that. However, there is definitely some value on using Dusk for integration & end to end tests. For those scenarios it's rarely necessary to mock any facades. But if you do find yourself in that situation, this package can help you :smiley:.

## How does it work?

The reason why mocking cannot be done like in normal http tests is because when Dusk runs a test it's really doing an actual request using a browser (running for example on chromedriver). Server and client don't share the same runtime, and that's why it isn't possible to have code communicate between them. Knowing this, how does this package work? Well, Dusk is already achieving something similar to this when using authentication. Some special routes (as a convention starting with `_dusk`) are created to provide communication with the server process, and state is persisted in the session like it would with a normal Laravel session. Given this and other uses, different drivers can be configured for tests (in your `.env.dusk` or `phpunit.dusk.xml` files). By using a separate session driver such as a testing database, it can be wiped out before and after each test to guarantee a real black-box scenario.

In a nutshell, what happens under the hood is that Facades are replaced using the [`swap`](https://laravel.com/api/5.6/Illuminate/Support/Facades/Facade.html#method_swap) method at the beginning of every request, and they'll be serialized at the end. When calling assertions methods from test code, data will be deserialized into the test runtime and assertions executed as usual.

You can learn more about how this works looking at [MockingProxy](src/MockingProxy.php), [Driver](src/Driver.php) and [MockingServiceProvider](src/MockingServiceProvider.php) classes.

## Limitations

The serialization/deserialization of mocked services is implemented using php's [serialize](https://www.php.net/manual/en/function.serialize.php) and [unserialize](https://www.php.net/manual/en/function.unserialize.php) functions. One limitation that this ensues is that closures can't be serialized. If you see an error saying `Serialization of 'Closure' is not allowed`, that means somewhere inside a service you're faking, there is a closure.

There is a couple of things you could do to work around this limitation.

If you have control over the service, you could use the [SerializableClosure](https://github.com/opis/closure) class to make your closures serializable (Laravel core is already doing this [in multiple places](https://github.com/laravel/framework/search?q=SerializableClosure)).

If you're trying to mock a 3rd party service you don't have control over, you could implement a service fake yourself instead of using the built-in class. This library [already does it](src/Fakes/) with some common Laravel services, but it doesn't cover the complete Laravel api. You can register your custom fakes using [the extending functionality](#extending). Although this may seem a daunting task, keep in mind that you only need to implement fakes for the functionality you're testing using this package. And as we discussed at the start of this section, that shouldn't be a lot.
