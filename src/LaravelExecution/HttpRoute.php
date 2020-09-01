<?php

namespace JKocik\Laravel\Profiler\LaravelExecution;

use Closure;
use ReflectionMethod;
use ReflectionFunction;
use ReflectionException;
use ReflectionParameter;
use Illuminate\Routing\Route;
use Illuminate\Support\Collection;
use Illuminate\Foundation\Http\FormRequest;
use JKocik\Laravel\Profiler\Contracts\ExecutionRoute;

class HttpRoute implements ExecutionRoute
{
    /**
     * @var Route|array
     */
    protected $route;

    /**
     * HttpRoute constructor.
     * @param Route|array $route
     */
    public function __construct($route)
    {
        $this->route = $route;
    }

    /**
     * @return Collection
     */
    public function meta(): Collection
    {
        return Collection::make();
    }

    /**
     * @return Collection
     */
    public function data(): Collection
    {
        if (is_array($this->route)) {
            return Collection::make($this->route);
        } else {
            return Collection::make([
                'methods' => $this->route->methods(),
                'uri' => $this->route->uri(),
                'name' => $this->route->getName(),
                'middleware' => $this->route->middleware(),
                'parameters' => $this->route->parameters(),
                'prefix' => $this->route->getPrefix(),
                'uses' => $this->uses(),
            ]);
        }
    }

    /**
     * @return array
     */
    protected function uses(): array
    {
        $uses = $this->route->getAction();

        try {
            if ($this->isClosureIn($uses)) {
                return $this->closure($uses);
            }

            if ($this->isControllerIn($uses)) {
                return $this->controller($uses);
            }
        } catch (ReflectionException $e) {}

        return [];
    }

    /**
     * @param array $uses
     * @return bool
     */
    protected function isClosureIn(array $uses): bool
    {
        return isset($uses['uses']) && $uses['uses'] instanceof Closure;
    }

    /**
     * @param array $uses
     * @return array
     */
    protected function closure(array $uses): array
    {
        $action = new ReflectionFunction($uses['uses']);

        return [
            'closure' => $action->getFileName() . ':' . $action->getStartLine() . '-' . $action->getEndLine(),
            'form_request' => $this->formRequest($action->getParameters()),
        ];
    }

    /**
     * @param array $uses
     * @return bool
     */
    protected function isControllerIn(array $uses): bool
    {
        return isset($uses['uses']) && count(explode('@', $uses['uses'])) == 2;
    }

    /**
     * @param array $uses
     * @return array
     */
    protected function controller(array $uses): array
    {
        list($controller, $method) = explode('@', $uses['uses']);

        $action = new ReflectionMethod($controller, $method);

        return [
            'controller' => $uses['uses'] . ':' . $action->getStartLine() . '-' . $action->getEndLine(),
            'form_request' => $this->formRequest($action->getParameters()),
        ];
    }

    /**
     * @param array $parameters
     * @return string
     */
    protected function formRequest(array $parameters): string
    {
        $formRequest = Collection::make($parameters)->filter(function (ReflectionParameter $parameter) {
            return $parameter->getClass() && $parameter->getClass()->isSubclassOf(FormRequest::class);
        })->first();

        return $formRequest ? $formRequest->getClass()->getName() : '';
    }
}
