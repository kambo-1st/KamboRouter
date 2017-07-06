<?php
declare(strict_types=1);

namespace Kambo\Router\Route;

use Kambo\Router\Route\Route;

/**
 * Route builder interface - all route builders must implement this interface.
 *
 * @package Kambo\Router\Route
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license MIT
 */
interface Builder
{
    /**
     * Build instance of the route
     *
     * @param string $method  Route method - GET, POST, etc...
     * @param string $url     route definition
     * @param mixed  $handler handler which will be executed if the url match
     *                        the route
     *
     * @return \Kambo\Router\Route\Route Instance implementing Route interface
     */
    public function build(string $method, string $url, $handler) : Route;
}
