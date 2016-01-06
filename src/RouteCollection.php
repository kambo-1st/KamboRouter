<?php

namespace Kambo\Router;

/**
 * Holds all defined routes
 *
 * @author   Bohuslav Simek <bohuslav@simek.si>
 * @version  GIT $Id$
 * @license  Apache-2.0
 * @category Router
 * @package  Router
 * 
 */

use Kambo\Router\Enum\Methods;

class RouteCollection 
{
    /**
     * Holds all routes
     *
     * @var array
     */    
    private $_routes = [];

    /**
     * Add route matched with GET method.
     * Shortcut for addRoute function with preset GET method.
     * 
     * @param mixed $route   route definition
     * @param mixed $handler handler that will be executed if the url will match the route
     *
     * @return self for fluent interface
     */
    public function get($route, $handler) {
        $this->addRoute(Methods::GET, $route, $handler);
        return $this;
    }

    /**
     * Add route matched with POST method.
     * Shortcut for addRoute function with preset POST method.
     * 
     * @param mixed $route   route definition
     * @param mixed $handler handler that will be executed if the url will match the route
     *
     * @return self for fluent interface
     */
    public function post($route, $handler) {
        $this->addRoute(Methods::POST, $route, $handler);
        return $this;
    }

    /**
     * Add route matched with DELETE method.
     * Shortcut for addRoute function with preset DELETE method.
     * 
     * @param mixed $route   route definition
     * @param mixed $handler handler that will be executed if the url will match the route
     *
     * @return self for fluent interface
     */
    public function delete($route, $handler) {
        $this->addRoute(Methods::DELETE, $route, $handler);
        return $this;
    }

    /**
     * Add route matched with PUT method.
     * Shortcut for addRoute function with preset PUT method.
     * 
     * @param mixed $route   route definition
     * @param mixed $handler handler that will be executed if the url will match the route
     *
     * @return self for fluent interface
     */
    public function put($route, $handler) {
        $this->addRoute(Methods::PUT, $route, $handler);
        return $this;
    }

    /**
     * Add route matched with ANY method.
     * Shortcut for addRoute function with preset ANY method.
     * 
     * @param mixed $route   route definition
     * @param mixed $handler handler that will be executed if the url will match the route
     *
     * @return self for fluent interface
     */
    public function any($route, $handler) {
        $this->addRoute(Methods::ANY, $route, $handler);
        return $this;
    }

    /**
     * Add route matched with POST method.
     * Shortcut for addRoute function with preset POST method.
     * 
     * @param mixed $route   route definition
     * @param mixed $handler handler that will be executed if the url will match the route
     *
     * @return self for fluent interface
     */
    public function addRoute($method, $route, $handler) {
        $this->_routes[] = [
            'method'  => $method,
            'handler' => $handler,
            'route'   => $route
        ];
        return $this;
    }

    /**
     * Get all defines routes in array.
     *
     * @return array
     */
    public function getRoutes() {
        return $this->_routes;
    }
}