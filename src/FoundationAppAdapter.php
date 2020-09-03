<?php

namespace JKocik\Laravel\Profiler;

use Laravel\Lumen\Application;

class FoundationAppAdapter extends Application
{
    protected $beforeBootstrappingCallbacks = [];
    protected $afterBootstrappingCallbacks = [];
    protected $bootingCallbacks = [];
    protected $bootedCallbacks = [];
    protected $terminatingCallbacks = [];

    protected function bootstrapContainer()
    {
        static::setInstance($this);

        $this->instance('app', $this);
        $this->instance(self::class, $this);
        $this->instance(Application::class, $this);

        $this->instance('path', $this->path());

        $this->instance('env', $this->environment());

        $this->registerContainerAliases();
    }

    public function boot()
    {
        if ($this->booted) {
            return;
        }

        $this->fireAppCallbacks($this->beforeBootstrappingCallbacks);
        $this->fireAppCallbacks($this->afterBootstrappingCallbacks);

        $this->fireAppCallbacks($this->bootingCallbacks);

        array_walk($this->loadedProviders, function ($p) {
            $this->bootProvider($p);
        });

        $this->fireAppCallbacks($this->bootedCallbacks);

        $this->booted = true;
    }

    public function beforeBootstrapping($bootstrapper, \Closure $callback)
    {
        $this->beforeBootstrappingCallbacks[] = $callback;
    }

    public function afterBootstrapping($bootstrapper, \Closure $callback)
    {
        $this->afterBootstrappingCallbacks[] = $callback;
    }

    public function booting($callback)
    {
        $this->bootingCallbacks[] = $callback;
    }

    public function booted($callback)
    {
        $this->bootedCallbacks[] = $callback;

        if ($this->booted) {
            $this->fireAppCallbacks([$callback]);
        }
    }

    public function terminating($callback)
    {
        $this->terminatingCallbacks[] = $callback;
    }

    public function terminate()
    {
        $this->fireAppCallbacks($this->terminatingCallbacks);
    }

    public function getLoadedProviders()
    {
        return $this->loadedProviders;
    }

    protected function fireAppCallbacks(array $callbacks)
    {
        foreach ($callbacks as $callback) {
            $callback($this);
        }
    }

    protected function langPath()
    {
        return $this->getLanguagePath();
    }
}
