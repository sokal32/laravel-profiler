<?php

namespace JKocik\Laravel\Profiler\LaravelListeners;

use Illuminate\Support\Facades\Event;
use Illuminate\Console\Events\ArtisanStarting;
use Illuminate\Console\Events\CommandFinished;
use JKocik\Laravel\Profiler\Contracts\ExecutionData;
use JKocik\Laravel\Profiler\Contracts\LaravelListener;
use JKocik\Laravel\Profiler\LaravelExecution\NullRequest;
use JKocik\Laravel\Profiler\LaravelExecution\NullResponse;
use JKocik\Laravel\Profiler\LaravelExecution\ConsoleStartingRequest;
use JKocik\Laravel\Profiler\LaravelExecution\ConsoleFinishedRequest;
use JKocik\Laravel\Profiler\LaravelExecution\ConsoleStartingResponse;
use JKocik\Laravel\Profiler\LaravelExecution\ConsoleFinishedResponse;

class ConsoleCommandFinishedListener implements LaravelListener
{
    /**
     * @var ExecutionData
     */
    protected $executionData;

    /**
     * ConsoleCommandFinishedListener constructor.
     * @param ExecutionData $executionData
     */
    public function __construct(ExecutionData $executionData)
    {
        $this->executionData = $executionData;
    }

    /**
     * @return void
     */
    public function listen(): void
    {
        Event::listen(ArtisanStarting::class, function ($event) {
            $this->executionData->setRequest(new ConsoleStartingRequest());
            $this->executionData->setResponse(new ConsoleStartingResponse());
        });

        Event::listen(CommandFinished::class, function ($event) {
            $this->executionData->setRequest(new ConsoleFinishedRequest($event->command, $event->input));
            $this->executionData->setResponse(new ConsoleFinishedResponse($event->exitCode));
        });
    }

    /**
     * @return void
     */
    public function forget(): void
    {
        Event::forget(ArtisanStarting::class);
        Event::forget(CommandFinished::class);

        $this->executionData->setRequest(new NullRequest());
        $this->executionData->setResponse(new NullResponse());
    }
}
