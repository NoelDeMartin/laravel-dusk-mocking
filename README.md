# Laravel Dusk Mocking

When running browser tests with Laravel Dusk, [it is not possible](https://github.com/laravel/dusk/issues/152) to mock facades like [it is usually done](https://laravel.com/docs/mocking) for http tests. This package aims to provide that functionality. However, it does so by doing some workarounds. It is recommended to read the [Disclaimer](#disclaimer) and [How does it work?](#how-does-it-work) sections on this readme before using it.

# Installation

Install using composer:

```
composer require --dev noeldemartin/laravel-dusk-mocking
```

Add the following code to your base test case (usually `DuskTestCase`).

```
use NoelDeMartin\LaravelDusk\Browser;

...

protected function newBrowser($driver)
{
    return new Browser($driver)
}
```

# Usage

The conceptual usage is the same as can be learned on Laravel's documentation about [mocking](https://laravel.com/docs/5.6/mocking). The only difference is that in Dusk, mocking can be set up independently on each browser instance. For that reason, instead of calling static methods we will call instance methods. Look at the following example on how to mock the Mail facade:

```
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

At the moment only the functionality analogous of calling `fake` is available, and not methods mocking. This will be addressed on [this issue](https://github.com/NoelDeMartin/laravel-dusk-mocking/issues/1).

# Disclaimer

Most scenarios should be covered with http tests, since they both run faster and are more reliable when testing your code. The same could be said when testing your frontend and Javascript, there are multiple frameworks specific for that. However, there is definetly some value on using Dusk for integration & end to end tests. For those scenarios it's rarely necessary to mock any facades. But if you do find yourself in that situation, this package can help you :smiley:.

# How does it work?

The reason why mocking cannot be done like in normal http tests is because when Dusk runs a test it's really doing an actual request to a different process (running for example on chromedriver). Server and client don't share the same runtime, and that's why it isn't possible to have code communicate between them. Knowing this, how does this package work? Well, Dusk is already achieving something similar to this when using authentication. Some special routes (as a convetion starting with `_dusk`) are created to provide communication with the server process, and state is persisted in the session like it would with a normal Laravel session. Given this and other uses, different drivers can be configured for tests (in your `.env.dusk` or `phpunit.dusk.xml` files). By using a separate session driver such as a testing database, it can be wiped out before and after each test to guarantee a real black-box scenario.

In a nutshell, what happens under the hood is that Facades are replaced using the [`swap`](https://laravel.com/api/5.6/Illuminate/Support/Facades/Facade.html#method_swap) method at the beginning of every request, and they'll be serialized at the end. When calling assertions methods from test code, data will be deserialized into the test runtime and assertions executed as usual.

You can learn more about how this works looking at [MockingProxy](src/MockingProxy.php), [Driver](src/Driver.php) and [MockingServiceProvider](src/MockingServiceProvider.php) classes.
