<?php
declare(strict_types=1);

namespace Kambo\Router\Route;

// spl
use IteratorAggregate;
use ArrayIterator;

// Kambo\Router\Enum
use Kambo\Router\Enum\Method;

// Kambo\Router\Route
use Kambo\Router\Route\Route;
use Kambo\Router\Route\Builder;

/**
 * Collection of all defined routes.
 *
 * @package Kambo\Router\Route
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license MIT
 */
class Collection implements IteratorAggregate
{
    /**
     * Contains all routes
     *
     * @var array
     */
    private $routes = [];

    /**
     * Contains all routes
     *
     * @var \Kambo\Router\Route\Builder
     */
    private $routeBuilder;

    /**
     * Route constructor
     *
     * @param \Kambo\Router\Route\Builder $routeBuilder Builder which will be used for creating
     *                                                  instance of the route object.
     */
    public function __construct(Builder $routeBuilder)
    {
        $this->routeBuilder = $routeBuilder;
    }

    /**
     * IteratorAggregate: returns the iterator object.
     *
     * @return ArrayIterator
     */
    public function getIterator() : ArrayIterator
    {
        return new ArrayIterator($this->routes);
    }

    /**
     * Add route matched with GET method.
     * Shortcut for createRoute function with preset GET method.
     *
     * @param mixed $route   route definition
     * @param mixed $handler handler which will be executed if the url match
     *                       the route
     *
     * @return \Kambo\Router\Route\Route Created route
     */
    public function get(string $route, $handler) : Route
    {
        return $this->createRoute(Method::GET, $route, $handler);
    }

    /**
     * Add route matched with POST method.
     * Shortcut for createRoute function with preset POST method.
     *
     * @param mixed $route   route definition
     * @param mixed $handler handler which will be executed if the url match
     *                       the route
     *
     * @return \Kambo\Router\Route\Route Created route
     */
    public function post(string $route, $handler) : Route
    {
        return $this->createRoute(Method::POST, $route, $handler);
    }

    /**
     * Add route matched with DELETE method.
     * Shortcut for createRoute function with preset DELETE method.
     *
     * @param mixed $route   route definition
     * @param mixed $handler handler which will be executed if the url match
     *                       the route
     *
     * @return \Kambo\Router\Route\Route Created route
     */
    public function delete(string $route, $handler) : Route
    {
        return $this->createRoute(Method::DELETE, $route, $handler);
    }

    /**
     * Add route matched with PUT method.
     * Shortcut for createRoute function with preset PUT method.
     *
     * @param mixed $route   route definition
     * @param mixed $handler handler which will be executed if the url match
     *                       the route
     *
     * @return \Kambo\Router\Route\Route Created route
     */
    public function put(string $route, $handler) : Route
    {
        return $this->createRoute(Method::PUT, $route, $handler);
    }

    /**
     * Add route which will be matched to any method.
     * Shortcut for createRoute function with preset ANY method.
     *
     * @param mixed $route   route definition
     * @param mixed $handler handler which will be executed if the url match
     *                       the route
     *
     * @return \Kambo\Router\Route\Route Created route
     */
    public function any(string $route, $handler) : Route
    {
        return $this->createRoute(Method::ANY, $route, $handler);
    }

    /**
     * Create a route in the collection.
     * The data structure used in the $handler depends on the used dispatcher.
     *
     * @param mixed $method  HTTP method which will be used for binding
     * @param mixed $route   route definition
     * @param mixed $handler handler which will be executed if the
     *                       url matchs the route
     *
     * @return \Kambo\Router\Route\Route Created route
     */
    public function createRoute(string $method, string $route, $handler) : Route
    {
        $createdRoute   = $this->routeBuilder->build($method, $route, $handler);
        $this->routes[] = $createdRoute;

        return $createdRoute;
    }

    /**
     * Add a route to the collection.
     *
     * @param Kambo\Router\Route\Route $route route which will be added into
     *                                        collection
     *
     * @return self for fluent interface
     */
    public function addRoute(Route $route) : Collection
    {
        $this->routes[] = $route;

        return $this;
    }
}
