<?php

namespace JKocik\Laravel\Profiler;

use JKocik\Laravel\Profiler\Middlewares\AppTerminateHook;
use JKocik\Laravel\Profiler\Contracts\Profiler;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use JKocik\Laravel\Profiler\Services\ConfigService;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * @return void
     */
    public function register(): void
    {
        $this->app->middleware([AppTerminateHook::class]);

        $this->mergeConfigFrom(static::profilerConfigPath(), 'profiler');

        $this->app->singleton(Profiler::class, function ($app) {
            return (new ProfilerResolver($app, $app->make(ConfigService::class)))->resolve();
        });

        $this->app->make(Profiler::class)->listenForBoot();
    }

    /**
     * @return void
     */
    public function boot(): void
    {
        $this->allowConfigFileToBePublished();
    }

    /**
     * @return void
     */
    public function allowConfigFileToBePublished(): void
    {
        $this->app->configure('profiler');
    }

    /**
     * @return string
     */
    public static function profilerConfigPath(): string
    {
        return __DIR__ . '/../config/profiler.php';
    }
}
