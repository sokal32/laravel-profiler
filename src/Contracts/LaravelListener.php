<?php

namespace JKocik\Laravel\Profiler\Contracts;

interface LaravelListener
{
    /**
     * @return void
     */
    public function listen(): void;

    /**
     * @return void
     */
    public function forget(): void;
}
