<?php

namespace JKocik\Laravel\Profiler\Middlewares;

class AppTerminateHook
{
    public function handle($request, $next)
    {
        return $next($request);
    }

    public function terminate($request, $response)
    {
        event('kernel.handled', ['request' => $request, 'response' => $response]);
        $GLOBALS['app']->terminate();
    }
}
