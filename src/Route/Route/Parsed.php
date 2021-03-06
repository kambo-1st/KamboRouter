<?php
declare(strict_types=1);

namespace Kambo\Router\Route\Route;

use Kambo\Router\Route\Route;

/**
 * Parsed route from matcher class.
 * Class is implemented as a proxy for existing Route object.
 *
 * @package Kambo\Router\Route\Route
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license MIT
 */
class Parsed
{
    /**
     * Instance of original route
     *
     * @var \Kambo\Router\Route\Route
     */
    private $route;

    /**
     * Route placeholders from route.
     *
     * @var array
     */
    private $placeholders;

    /**
     * Route parameters from request
     *
     * @var array
     */
    private $parameters;

    /**
     * ParsedRoute constructor
     *
     * @param \Kambo\Router\Route\Route $route Existing route which will be used as a base for proxy.
     */
    public function __construct(Route $route)
    {
        $this->route = $route;
    }

    /**
     * Magic method for proxing methods call to parent route.
     *
     * @param string $name      Method name
     * @param array  $arguments The parameters to be passed to the method,
     *                          as an indexed array.
     *
     * @return mixed
     */
    public function __call(string $name, array $arguments)
    {
        return call_user_func_array([$this->route, $name], $arguments);
    }

    /**
     * Get route method
     *
     * @return string
     */
    public function getMethod() : string
    {
        return $this->route->getMethod();
    }

    /**
     * Get handler
     *
     * @return mixed
     */
    public function getHandler()
    {
        return $this->route->getHandler();
    }

    /**
     * Sets placeholders extracted from route.
     *
     * @param arrat $placeholders
     *
     * @return self for fluent interface
     */
    public function setPlaceholders(array $placeholders) : Parsed
    {
        $this->placeholders = $placeholders;

        return $this;
    }

    /**
     * Get placeholders extracted from route.
     *
     * @return array
     */
    public function getPlaceholders() : array
    {
        return $this->placeholders;
    }

    /**
     * Sets parameters for route from request.
     *
     * @param mixed $parameters
     *
     * @return self for fluent interface
     */
    public function setParameters(array $parameters) : Parsed
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * Get parameters of route from request.
     *
     * @return array
     */
    public function getParameters() : array
    {
        return $this->parameters;
    }
}
