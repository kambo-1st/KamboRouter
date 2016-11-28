<?php
namespace Kambo\Router\Route;

/**
 * Class representing the Route
 *
 * @author   Bohuslav Simek <bohuslav@simek.si>
 * @license  Apache-2.0
 * @package  Kambo\Router\Route
 */
class Route
{
    private $method  = null;
    private $handler = null;
    private $url     = null;

    private $parsedRoute = null;
    private $parameters  = null;

    /**
     * Route constructor
     *
     * @param String $method
     * @param String $url
     * @param Mixed  $handler
     */
    public function __construct($method, $url, $handler)
    {
        $this->method  = $method;
        $this->handler = $handler;
        $this->url     = $url;
    }

    /**
     * Sets route method
     *
     * @param string $method route method
     *
     * @return self for fluent interface
     */
    public function setMethod($method)
    {
        $this->method = $method;

        return $this;
    }

    /**
     * Get route method
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Sets URL for route
     *
     * @param mixed $url route url
     *
     * @return self for fluent interface
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get URL of route
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Sets handler that will be executed if the url will match the route.
     *
     * @param mixed $handler handler which will be executed if the url will
     *                       match the route
     *
     * @return self for fluent interface
     */
    public function setHandler($handler)
    {
        $this->handler = $handler;

        return $this;
    }

    /**
     * Get handler
     *
     * @return mixed
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * Sets parsed route.
     *
     * @param mixed $parsed
     *
     * @return self for fluent interface
     */
    public function setParsed($parsed)
    {
        $this->parsedRoute = $parsed;

        return $this;
    }

    /**
     * Get parsed route.
     *
     * @return string
     */
    public function getParsed()
    {
        return $this->parsedRoute;
    }

    /**
     * Sets parameters for route.
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
     * Get parameters of route.
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }
}
