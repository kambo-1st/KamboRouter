<?php
namespace Kambo\Router\Dispatchers;

// \Spl
use Closure;
use InvalidArgumentException;
use ReflectionFunction;

// \Kambo\Router
use Kambo\Router\Dispatchers\Interfaces\DispatcherInterface;
use Kambo\Router\Route\ParsedRoute;

/**
 * Dispatcher with closure support
 *
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license Apache-2.0
 * @package Kambo\Router\Dispatchers
 */
class DispatcherClosure implements DispatcherInterface
{
    /**
     * Not found handler will be called if nothing has been found.
     *
     * @var \Closure
     */
    private $notFoundHandler;

    /**
     * Dispatch found route with given parameters
     *
     * @param ParsedRoute $route found route
     *
     * @return mixed
     */
    public function dispatchRoute(ParsedRoute $route)
    {
        $handler = $route->getHandler();
        if ($this->isClosure($handler)) {
            $paramMap  = $this->getFunctionArgumentsNames($handler);
            $arguments = $this->getFunctionArguments(
                $paramMap,
                $route->getParameters(),
                $route->getPlaceholders()
            );

            return call_user_func_array($handler, $arguments);
        } else {
            return $this->dispatchNotFound();
        }
    }

    /**
     * Called if any of route did not match the request.
     * Call the defined handler or simply do nothing if the handler is not
     * specified.
     *
     * @return mixed|null
     */
    public function dispatchNotFound()
    {
        if (isset($this->notFoundHandler)) {
            return call_user_func($this->notFoundHandler);
        }

        return null;
    }

    /**
     * Sets not found handler
     *
     * @param string $handler handler that will be excuted if nothing has been
     *                        found
     *
     * @return self for fluent interface
     *
     * @throws InvalidArgumentException if the provided value is not closure
     */
    public function setNotFoundHandler($handler)
    {
        if (!$this->isClosure($handler)) {
            throw new InvalidArgumentException(
                'Handler must be closure'
            );
        }

        $this->notFoundHandler = $handler;

        return $this;
    }

    // ------------ PRIVATE METHODS

    /**
     * Check if variable is closure
     *
     * @param mixed $type variable to check
     *
     * @return boolean return true if is
     */
    private function isClosure($type)
    {
        return is_object($type) && ($type instanceof Closure);
    }

    /**
     * Get arguments for closure function in proper order
     * from provided parameters
     *
     * @param mixed $paramMap   parameter map for getting proper order
     * @param mixed $matches    parameters from request
     * @param mixed $parameters expected parameters from route
     *
     * @return array Parameters in right order, if there are not any
     *         parametrs an empty array is returned.
     */
    private function getFunctionArguments($paramMap, $matches, $parameters)
    {
        $output  = [];
        $matches = array_values($matches);
        if (isset($parameters)) {
            foreach ($parameters as $valueName) {
                foreach ($paramMap as $possition => $value) {
                    if ($value == $valueName[1][0]) {
                        $output[] = $matches[$possition];
                    }
                }
            }
        }

        return $output;
    }

    /**
     * Get name of parameters for provided closure
     *
     * @param \Closure $closure
     *
     * @return array
     */
    private function getFunctionArgumentsNames($closure)
    {
        $closureReflection = new ReflectionFunction($closure);
        $result            = [];
        foreach ($closureReflection->getParameters() as $param) {
            $result[] = $param->name;
        }

        return $result;
    }
}
