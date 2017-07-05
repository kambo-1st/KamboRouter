<?php
declare(strict_types=1);

namespace Kambo\Router\Route\Route;

use Kambo\Router\Route\Route;

/**
 * Class representing the base Route
 *
 * @package Kambo\Router\Route\Route
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license MIT
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
    public function __construct(string $method, string $url, $handler)
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
    public function setMethod(string $method) : Route
    {
        $this->method = $method;

        return $this;
    }

    /**
     * Get route method
     *
     * @return string
     */
    public function getMethod() : string
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
    public function setUrl(string $url) : Route
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get URL of route
     *
     * @return string
     */
    public function getUrl() : string
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
