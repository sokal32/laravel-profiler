<?php

namespace JKocik\Laravel\Profiler\Services;

use Illuminate\Config\Repository;
use Illuminate\Support\Collection;
use Laravel\Lumen\Application;

class ConfigService
{
    /**
     * @var Application
     */
    protected $app;

    /**
     * @var Repository
     */
    protected $config;

    /**
     * ConfigService constructor.
     * @param Application $app
     * @param Repository $config
     */
    public function __construct(Application $app, Repository $config)
    {
        $this->app = $app;
        $this->config = $config;
    }

    /**
     * @return bool
     */
    public function isProfilerEnabled(): bool
    {
        $enabledOverrides = Collection::make(config('profiler.enabled_overrides'));
        $envToDisable = $enabledOverrides->filter(function ($enabled) {
            return ! $enabled;
        })->keys();

        if ($this->app->environment($envToDisable->toArray())) {
            return false;
        }

        return config('profiler.enabled') === true;
    }

    /**
     * @return Collection
     */
    public function trackers(): Collection
    {
        return Collection::make(config('profiler.trackers'));
    }

    /**
     * @return Collection
     */
    public function processors(): Collection
    {
        return Collection::make(config('profiler.processors'));
    }

    /**
     * @param array $processors
     */
    public function overrideProcessors(array $processors): void
    {
        $this->config->set('profiler.processors', $processors);
    }

    /**
     * @return Collection
     */
    public function pathsToTurnOffProcessors(): Collection
    {
        return Collection::make(config('profiler.turn_off_processors_for_paths'));
    }

    /**
     * @return string
     */
    public function serverHttpConnectionUrl(): string
    {
        $address = 'core-service'; //config('profiler.server_http.address');
        $port = config('profiler.server_http.port');

        return  $address . ':' . $port;
    }

    /**
     * @return string
     */
    public function serverHttpPort(): string
    {
        return config('profiler.server_http.port');
    }

    /**
     * @return string
     */
    public function serverSocketsPort(): string
    {
        return config('profiler.server_sockets.port');
    }

    /**
     * @return bool
     */
    public function isViewsDataEnabled(): bool
    {
        return config('profiler.data.views');
    }

    /**
     * @return bool
     */
    public function isEventsDataEnabled(): bool
    {
        return config('profiler.data.events');
    }

    /**
     * @return bool
     */
    public function isEventsGroupEnabled(): bool
    {
        return config('profiler.group.events');
    }

    /**
     * @param int $level
     * @return bool
     */
    public function handleExceptions(int $level): bool
    {
        return config('profiler.handle_exceptions') === $level;
    }
}
