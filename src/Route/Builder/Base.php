<?php
declare(strict_types=1);

namespace Kambo\Router\Route\Builder;

use Kambo\Router\Route\Builder;
use Kambo\Router\Route\Route\Base as BaseRoute;
use Kambo\Router\Route\Route;

/**
 * Build instance of the base route
 *
 * @package Kambo\Router\Route
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license MIT
 */
class Base implements Builder
{
    /**
     * Build instance of the route
     *
     * @param string $method  Route method - GET, POST, etc...
     * @param string $url     route definition
     * @param mixed  $handler handler which will be executed if the url match
     *                        the route
     *
     * @return \Kambo\Router\Route\Route\Base Base route
     */
    public function build(string $method, string $url, $handler) : Route
    {
        return new BaseRoute($method, $url, $handler);
    }
}
