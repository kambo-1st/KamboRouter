<?php

namespace Kambo\Router\Route\Route;

use Kambo\Router\Route\Route;

/**
 * Class representing the base Route
 *
 * @package Kambo\Router\Route\Route
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license Apache-2.0
 */
class Base implements Route
{
    /**
     * Route methog, eg.: GET, POST, ...
     *
     * @var string
     */
    private $method;

    /**
     * Route handler, can be callable or array, this is connected
     * with the selected dispatcher.
     *
     * @var mixed
     */
    private $handler;

    /**
     * Route url eg. /foo/bar
     *
     * @var string
     */
    private $url;

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
}
