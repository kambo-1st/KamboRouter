<?php
namespace Kambo\Router\Route\Builder;

use Kambo\Router\Route\Builder;
use Kambo\Router\Route\Route\Base as BaseRoute;

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
     * @param mixed $method  Route method - GET, POST, etc...
     * @param mixed $url     route definition
     * @param mixed $handler handler which will be executed if the url match
     *                       the route
     *
     * @return \Kambo\Router\Route\Route\Base Base route
     */
    public function build($method, $url, $handler)
    {
        return new BaseRoute($method, $url, $handler);
    }
}
