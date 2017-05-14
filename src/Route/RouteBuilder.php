<?php

namespace Kambo\Router\Route;

use Kambo\Router\Route\Route;

/**
 * Build instance of the route
 *
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license Apache-2.0
 * @package Kambo\Router\Route
 */
class RouteBuilder
{
    public function build($method, $route, $handler)
    {
        return new Route($method, $route, $handler);
    }
}
