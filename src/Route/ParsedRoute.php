<?php

namespace Kambo\Router\Route;

/**
 * Parsed route from matcher class.
 * Class is implemented as a proxy for existing Route object.
 *
 * @package Kambo\Router\Route
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license MIT
 */
class ParsedRoute
{
    /**
     * Instance of original route
     *
     * @var \Kambo\Router\Route
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
     * @param \Kambo\Router\Route $route
     */
    public function __construct(Route $route)
    {
        $this->route = $route;
    }

    /**
     * Get route method
     *
     * @return string
     */
    public function getMethod()
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
     * @param mixed $parameters
     *
     * @return self for fluent interface
     */
    public function setPlaceholders($placeholders)
    {
        $this->placeholders = $placeholders;

        return $this;
    }

    /**
     * Get placeholders extracted from route.
     *
     * @return array
     */
    public function getPlaceholders()
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
    public function setParameters($parameters)
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * Get parameters of route from request.
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }
}
