<?php

namespace NoelDeMartin\LaravelDusk\Http\Middleware;

use Closure;
use NoelDeMartin\LaravelDusk\Facades\Mocking;

class StartMocking
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        Mocking::start();

        return $next($request);
    }
}
