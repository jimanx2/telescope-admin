<?php namespace Laravel\Telescope\Http\Middleware;

use Laravel\Telescope\Http\Events\RequestHandled;

class EnsureRequestEvented
{
    public function handle($request, \Closure $next) {
        $response = $next($request);

        event(new RequestHandled($request, $response));

        return $response;
    }
}