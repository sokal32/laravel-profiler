<?php

namespace JKocik\Laravel\Profiler\Tests\Feature;

use Illuminate\Support\Facades\Event;
use JKocik\Laravel\Profiler\Tests\TestCase;
use Illuminate\Console\Events\ArtisanStarting;
use Illuminate\Console\Events\CommandFinished;
use JKocik\Laravel\Profiler\Contracts\ExecutionData;
use Illuminate\Foundation\Http\Events\RequestHandled;
use JKocik\Laravel\Profiler\LaravelExecution\NullRoute;
use JKocik\Laravel\Profiler\LaravelExecution\NullServer;
use JKocik\Laravel\Profiler\LaravelExecution\NullContent;
use JKocik\Laravel\Profiler\LaravelExecution\NullRequest;
use JKocik\Laravel\Profiler\LaravelExecution\NullSession;
use JKocik\Laravel\Profiler\LaravelExecution\NullResponse;

class LaravelExecutionTest extends TestCase
{
    /**
     * @return void
     */
    protected function setUp()
    {
        parent::setUp();

        $this->turnOffProcessors();
    }

    /** @test */
    function laravel_execution_data_is_singleton()
    {
        $executionDataA = $this->app->make(ExecutionData::class);
        $executionDataB = $this->app->make(ExecutionData::class);

        $this->assertSame($executionDataA, $executionDataB);
    }

    /** @test */
    function forgets_listener_after_terminate()
    {
        $executionData = $this->app->make(ExecutionData::class);
        $this->get('/');

        $this->app->terminate();
        $this->assertFalse(Event::hasListeners('kernel.handled'));
        $this->assertFalse(Event::hasListeners(RequestHandled::class));
        $this->assertFalse(Event::hasListeners(ArtisanStarting::class));
        $this->assertFalse(Event::hasListeners(CommandFinished::class));

        $this->assertInstanceOf(NullRequest::class, $executionData->request());
        $this->assertInstanceOf(NullRoute::class, $executionData->route());
        $this->assertInstanceOf(NullSession::class, $executionData->session());
        $this->assertInstanceOf(NullServer::class, $executionData->server());
        $this->assertInstanceOf(NullResponse::class, $executionData->response());
        $this->assertInstanceOf(NullContent::class, $executionData->content());
    }
}
