<?php
declare(strict_types=1);

namespace Kambo\Router\Dispatcher;

// \Spl
use Closure;
use InvalidArgumentException;
use ReflectionFunction;

// \Kambo\Router
use Kambo\Router\Dispatcher;
use Kambo\Router\Route\Route\Parsed;

/**
 * Dispatcher with closure support
 *
 * @package Kambo\Router\Dispatcher
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license MIT
 */
class ClosureAutoBind implements Dispatcher
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
     * @param \Kambo\Router\Route\Route\Parsed $route      Instance of found and parsed route.
     * @param array                            $parameters Additional parameters.
     *
     * @return mixed|null
     */
    public function dispatchRoute(Parsed $route, array $parameters = [])
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
        }

        return $this->dispatchNotFound();
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
     * @param Closure $handler handler that will be excuted if nothing has been
     *                        found
     *
     * @return self for fluent interface
     *
     * @throws InvalidArgumentException if the provided value is not closure
     */
    public function setNotFoundHandler($handler) : ClosureAutoBind
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
    private function isClosure($type) : bool
    {
        return is_object($type) && ($type instanceof Closure);
    }

    /**
     * Get arguments for closure function in proper order
     * from provided parameters
     *
     * @param array $paramMap   parameter map for getting proper order
     * @param array $matches    parameters from request
     * @param array $parameters expected parameters from route
     *
     * @return array Parameters in right order, if there are not any
     *               parametrs an empty array is returned.
     */
    private function getFunctionArguments(array $paramMap, array $matches, array $parameters) : array
    {
        $output  = [];
        $matches = array_values($matches);

        foreach ($parameters as $valueName) {
            foreach ($paramMap as $possition => $value) {
                if ($value == $valueName[1][0]) {
                    $output[] = $matches[$possition];
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
    private function getFunctionArgumentsNames($closure) : array
    {
        $result = [];

        $closureReflection = new ReflectionFunction($closure);

        foreach ($closureReflection->getParameters() as $param) {
            $result[] = $param->name;
        }

        return $result;
    }
}
