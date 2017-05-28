<?php

namespace Kambo\Router\Route;

/**
 * Route interface - all routes must implement this interface.
 *
 * @package Kambo\Router\Route
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license Apache-2.0
 */
interface Route
{
    /**
     * Sets route method
     *
     * @param string $method route method
     *
     * @return self for fluent interface
     */
    public function setMethod($method);

    /**
     * Get route method
     *
     * @return string
     */
    public function getMethod();

    /**
     * Sets URL for route
     *
     * @param mixed $url route url
     *
     * @return self for fluent interface
     */
    public function setUrl($url);

    /**
     * Get URL of route
     *
     * @return string
     */
    public function getUrl();

    /**
     * Sets handler that will be executed if the url will match the route.
     *
     * @param mixed $handler handler which will be executed if the url will
     *                       match the route
     *
     * @return self for fluent interface
     */
    public function setHandler($handler);

    /**
     * Get handler
     *
     * @return mixed
     */
    public function getHandler();
}
