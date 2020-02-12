<?php

namespace JKocik\Laravel\Profiler;

use JKocik\Laravel\Profiler\Contracts\ExecutionData;
use JKocik\Laravel\Profiler\Contracts\ExecutionWatcher;
use JKocik\Laravel\Profiler\LaravelExecution\NullRoute;
use JKocik\Laravel\Profiler\LaravelExecution\NullServer;
use JKocik\Laravel\Profiler\LaravelExecution\NullSession;
use JKocik\Laravel\Profiler\LaravelExecution\NullContent;
use JKocik\Laravel\Profiler\LaravelExecution\NullRequest;
use JKocik\Laravel\Profiler\LaravelExecution\NullResponse;
use JKocik\Laravel\Profiler\LaravelListeners\HttpRequestHandledListener;
use JKocik\Laravel\Profiler\LaravelListeners\ConsoleCommandFinishedListener;

class LaravelExecutionWatcher implements ExecutionWatcher
{
    /**
     * @var HttpRequestHandledListener
     */
    protected $httpRequestHandledListener;

    /**
     * @var ConsoleCommandFinishedListener
     */
    protected $consoleCommandFinishedListener;

    /**
     * @var ExecutionData
     */
    protected $executionData;

    /**
     * LaravelExecutionWatcher constructor.
     * @param HttpRequestHandledListener $httpRequestHandledListener
     * @param ConsoleCommandFinishedListener $consoleCommandFinishedListener
     * @param ExecutionData $executionData
     */
    public function __construct(
        HttpRequestHandledListener $httpRequestHandledListener,
        ConsoleCommandFinishedListener $consoleCommandFinishedListener,
        ExecutionData $executionData
    ) {
        $this->httpRequestHandledListener = $httpRequestHandledListener;
        $this->consoleCommandFinishedListener = $consoleCommandFinishedListener;
        $this->executionData = $executionData;
    }

    /**
     * @return void
     */
    public function watch(): void
    {
        $this->httpRequestHandledListener->listen();
        $this->consoleCommandFinishedListener->listen();
    }

    /**
     * @return void
     */
    public function forget(): void
    {
        $this->httpRequestHandledListener->forget();
        $this->consoleCommandFinishedListener->forget();

        $this->executionData->setRequest(new NullRequest());
        $this->executionData->setRoute(new NullRoute());
        $this->executionData->setSession(new NullSession());
        $this->executionData->setServer(new NullServer());
        $this->executionData->setResponse(new NullResponse());
        $this->executionData->setContent(new NullContent());
    }
}
