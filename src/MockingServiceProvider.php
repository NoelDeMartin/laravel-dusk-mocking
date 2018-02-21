<?php

namespace NoelDeMartin\LaravelDusk;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
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
            ->group(function() {
                Route::get('/_dusk-mocking/mock', [
                    'middleware' => 'web',
                    'uses' => 'MockingController@mock',
                ]);

                Route::get('/_dusk-mocking/serialize', [
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
        $this->app->singleton('dusk-mocking', function ($app) {
            return new MockingManager($app);
        });

        if (!$this->app->runningInConsole()) {
            $kernel = $this->app->make(HttpKernel::class);
            $kernel->pushMiddleware(StartMocking::class);
            $kernel->pushMiddleware(SaveMocking::class);
        }
    }
}
