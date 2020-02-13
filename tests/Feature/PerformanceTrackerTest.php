<?php

namespace JKocik\Laravel\Profiler\Tests\Feature;

use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Routing\Events\RouteMatched;
use JKocik\Laravel\Profiler\Tests\TestCase;
use JKocik\Laravel\Profiler\Events\Tracking;
use JKocik\Laravel\Profiler\Contracts\Timer;
use Illuminate\Console\Events\ArtisanStarting;
use JKocik\Laravel\Profiler\Events\Terminating;
use JKocik\Laravel\Profiler\Tests\Support\PHPMock;
use Illuminate\Foundation\Http\Events\RequestHandled;
use JKocik\Laravel\Profiler\Tests\Support\Fixtures\DummyCommand;
use JKocik\Laravel\Profiler\Tests\Support\Fixtures\PerformanceProcessor;

class PerformanceTrackerTest extends TestCase
{
    /**
     * @return void
     */
    protected function setUp()
    {
        parent::setUp();

        $this->addFixturePerformanceProcessor();
    }

    /**
     * @return void
     */
    protected function addFixturePerformanceProcessor(): void
    {
        $this->app = $this->appWith(function (Application $app) {
            $app->make('config')->set('profiler.processors', [
                PerformanceProcessor::class,
            ]);
            $app->singleton(PerformanceProcessor::class, function () {
                return new PerformanceProcessor();
            });
        });
    }

    /** @test */
    function has_laravel_total_execution_time()
    {
        $this->app->terminate();
        $timer = $this->app->make(Timer::class);
        $processor = $this->app->make(PerformanceProcessor::class);

        $this->assertTrue($processor->performance->has('timer'));
        $this->assertGreaterThan(0, $timer->milliseconds('laravel'));
        $this->assertEquals($timer->milliseconds('laravel'), $processor->performance->get('timer')['laravel']);
    }

    /** @test */
    function has_booting_time()
    {
        $this->app->terminate();
        $timer = $this->app->make(Timer::class);
        $processor = $this->app->make(PerformanceProcessor::class);

        $this->assertGreaterThan(0, $timer->milliseconds('boot'));
        $this->assertEquals($timer->milliseconds('boot'), $processor->performance->get('timer')['boot']);
    }

    /** @test */
    function has_route_time()
    {
        $this->get('/');
        $timer = $this->app->make(Timer::class);
        $processor = $this->app->make(PerformanceProcessor::class);

        $this->assertGreaterThan(0, $timer->milliseconds('route'));
        $this->assertEquals($timer->milliseconds('route'), $processor->performance->get('timer')['route']);
    }

    /** @test */
    function has_setup_time_instead_of_route_when_testing()
    {
        putenv('APP_ENV=testing');
        $_ENV['APP_ENV'] = 'testing';
        $this->app = $this->app();
        $this->addFixturePerformanceProcessor();

        $this->get('/');
        $timer = $this->app->make(Timer::class);
        $processor = $this->app->make(PerformanceProcessor::class);

        $this->assertGreaterThan(0, $timer->milliseconds('setup'));
        $this->assertEquals($timer->milliseconds('setup'), $processor->performance->get('timer')['setup']);
    }

    /** @test */
    function has_handle_request_time()
    {
        $this->get('/');
        $timer = $this->app->make(Timer::class);
        $processor = $this->app->make(PerformanceProcessor::class);

        $this->assertGreaterThan(0, $timer->milliseconds('request'));
        $this->assertEquals($timer->milliseconds('request'), $processor->performance->get('timer')['request']);
    }

    /** @test */
    function has_send_response_and_terminate_time()
    {
        $this->get('/');
        $timer = $this->app->make(Timer::class);
        $processor = $this->app->make(PerformanceProcessor::class);

        $this->assertGreaterThan(0, $timer->milliseconds('response'));
        $this->assertEquals($timer->milliseconds('response'), $processor->performance->get('timer')['response']);
    }

    /** @test */
    function has_console_time()
    {
        $this->app->make(Kernel::class)->registerCommand(new DummyCommand(0));
        Artisan::call('dummy-command');
        $this->app->terminate();
        $timer = $this->app->make(Timer::class);
        $processor = $this->app->make(PerformanceProcessor::class);

        $this->assertGreaterThan(0, $timer->milliseconds('command'));
        $this->assertEquals($timer->milliseconds('command'), $processor->performance->get('timer')['command']);
    }

    /** @test */
    function has_memory_peak()
    {
        $this->app->terminate();
        $processor = $this->app->make(PerformanceProcessor::class);

        $this->assertEquals(PHPMock::MEMORY_USAGE, $processor->performance->get('memory')['peak']);
    }

    /** @test */
    function forgets_listener_after_terminate()
    {
        $this->app->terminate();
        $this->assertFalse(Event::hasListeners(Tracking::class));
        $this->assertFalse(Event::hasListeners(RouteMatched::class));
        $this->assertFalse(Event::hasListeners('kernel.handled'));
        $this->assertFalse(Event::hasListeners(RequestHandled::class));
        $this->assertFalse(Event::hasListeners(Terminating::class));
        $this->assertFalse(Event::hasListeners(ArtisanStarting::class));
    }

    /**
     * @return void
     */
    protected function tearDown()
    {
        parent::tearDown();

        putenv('APP_ENV=local');
        unset($_ENV['APP_ENV']);
    }
}
