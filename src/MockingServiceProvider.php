<?php

namespace NoelDeMartin\LaravelDusk;

use Exception;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use NoelDeMartin\LaravelDusk\Http\Middleware\SaveMocking;
use NoelDeMartin\LaravelDusk\Http\Middleware\StartMocking;

class MockingServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        Route::namespace('NoelDeMartin\LaravelDusk\Http\Controllers')
            ->group(function () {
                Route::get('/_dusk-mocking/csrf_token', [
                    'middleware' => 'web',
                    'uses' => 'MockingController@csrfToken',
                ]);

                Route::post('/_dusk-mocking/mock', [
                    'middleware' => 'web',
                    'uses' => 'MockingController@mock',
                ]);

                Route::post('/_dusk-mocking/register', [
                    'middleware' => 'web',
                    'uses' => 'MockingController@register',
                ]);

                Route::post('/_dusk-mocking/serialize', [
                    'middleware' => 'web',
                    'uses' => 'MockingController@serialize',
                ]);
            });
    }

    /**
     * Register any package services.
     *
     * @return void
     * @throws Exception
     */
    public function register()
    {
        if ($this->app->environment('production')) {
            throw new Exception('It is unsafe to run Dusk in production.');
        }

        $this->app->singleton('dusk-mocking', function ($app) {
            return new MockingManager($app);
        });

        if (! $this->app->runningInConsole()) {
            foreach (Route::getMiddlewareGroups() as $group => $middlewares) {
                Route::pushMiddlewareToGroup($group, StartMocking::class);
                Route::pushMiddlewareToGroup($group, SaveMocking::class);
            }
        }
    }
}
