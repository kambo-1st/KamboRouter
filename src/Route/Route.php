<?php
declare(strict_types=1);

namespace Kambo\Router\Route;

/**
 * Route interface - all routes must implement this interface.
 *
 * @package Kambo\Router\Route
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license MIT
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
    public function setMethod(string $method) : Route;

    /**
     * Get route method
     *
     * @return string
     */
    public function getMethod() : string;

    /**
     * Sets URL for route
     *
     * @param mixed $url route url
     *
     * @return self for fluent interface
     */
    public function setUrl(string $url) : Route;

    /**
     * Get URL of route
     *
     * @return string
     */
    public function getUrl() : string;

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
