<?php

namespace Kambo\Router\Route;

/**
 * A container for all defined routes.
 *
 * @author   Bohuslav Simek <bohuslav@simek.si>
 * @version  GIT $Id$
 * @license  Apache-2.0
 * @category Route
 * @package  Router
 * 
 */

use Kambo\Router\Enum\Method;
use Kambo\Router\Route\Route;

class RouteCollection 
{
    /**
     * Contains all routes
     *
     * @var array
     */    
    private $_routes = [];

    /**
     * Add route matched with GET method.
     * Shortcut for createRoute function with preset GET method.
     * 
     * @param mixed $route   route definition
     * @param mixed $handler handler that will be executed if the url will match the route
     *
     * @return self for fluent interface
     */
    public function get($route, $handler) {
        $this->createRoute(Method::GET, $route, $handler);

        return $this;
    }

    /**
     * Add route matched with POST method.
     * Shortcut for createRoute function with preset POST method.
     * 
     * @param mixed $route   route definition
     * @param mixed $handler handler that will be executed if the url will match the route
     *
     * @return self for fluent interface
     */
    public function post($route, $handler) {
        $this->createRoute(Method::POST, $route, $handler);

        return $this;
    }

    /**
     * Add route matched with DELETE method.
     * Shortcut for createRoute function with preset DELETE method.
     * 
     * @param mixed $route   route definition
     * @param mixed $handler handler that will be executed if the url will match the route
     *
     * @return self for fluent interface
     */
    public function delete($route, $handler) {
        $this->createRoute(Method::DELETE, $route, $handler);

        return $this;
    }

    /**
     * Add route matched with PUT method.
     * Shortcut for createRoute function with preset PUT method.
     * 
     * @param mixed $route   route definition
     * @param mixed $handler handler that will be executed if the url will match the route
     *
     * @return self for fluent interface
     */
    public function put($route, $handler) {
        $this->createRoute(Method::PUT, $route, $handler);

        return $this;
    }

    /**
     * Add route that will be matched to any method.
     * Shortcut for createRoute function with preset ANY method.
     * 
     * @param mixed $route   route definition
     * @param mixed $handler handler that will be executed if the url will match the route
     *
     * @return self for fluent interface
     */
    public function any($route, $handler) {
        $this->createRoute(Method::ANY, $route, $handler);

        return $this;
    }

    /**
     * Create a route to the collection.
     * The data structure used in the $handler depends on the used dispatcher.
     * 
     * @param mixed $method  HTTP method that will be used for binding
     * @param mixed $route   route definition
     * @param mixed $handler handler that will be executed if the url will match the route
     *
     * @return self for fluent interface
     */
    public function createRoute($method, $route, $handler) {
        $this->_routes[] = new Route($method, $route, $handler);

        return $this;
    }

    /**
     * Add a route to the collection.
     * 
     * @param Kambo\Router\Route\Route $route route that will be added into collection
     *
     * @return self for fluent interface
     */
    public function addRoute(Route $route) {
        $this->_routes[] = $route;

        return $this;
    }

    /**
     * Get all defines routes in collection.
     *
     * @return Route[]
     */
    public function getRoutes() {
        return $this->_routes;
    }
}