<?php

namespace Kambo\Router;

// \Psr\Http\Message
use Psr\Http\Message\ServerRequestInterface as ServerRequest;

//  \Kambo\Router
use Kambo\Router\Route\Collection;
use Kambo\Router\Dispatchers\Interfaces\DispatcherInterface;

/**
 * Match provided request object with all defined routes in route collection.
 * If some of routes match a data in provided request. Route is dispatched
 * with additionall parameters. If nothing is matched execution is passed to
 * specific function in dispatcher
 *
 * @package Kambo\Router
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license Apache-2.0
 */
class Router
{
    /**
     * Instance of route collection
     *
     * @var \Kambo\Router\Route\Collection
     */
    private $routes;

    /**
     * Instance of Dispatcher which will dispatch the request
     *
     * @var \Kambo\Router\Dispatchers\Interfaces\DispatcherInterface
     */
    private $dispatcher;

    /**
     * Defualt constructor
     *
     * @param \Kambo\Router\Route\Collection                           $routeCollection
     * @param \Kambo\Router\Dispatchers\Interfaces\DispatcherInterface $dispatcher
     *
     */
    public function __construct(
        Collection $routeCollection,
        DispatcherInterface $dispatcher,
        $matcher
    ) {
        $this->routes     = $routeCollection;
        $this->dispatcher = $dispatcher;
        $this->matcher    = $matcher;
    }

    /**
     * Match request with provided routes.
     * Get method and url from provided request and start matching.
     *
     * @param ServerRequest $request instance of PSR 7 compatible request object
     *
     * @return mixed
     */
    public function matchRequest(ServerRequest $request, array $parameters)
    {
        $matchedRoute = $this->matcher->matchRequest($request);
        if ($matchedRoute !== false) {
            return $this->dispatcher->dispatchRoute($matchedRoute, $parameters);
        }

        return $this->dispatcher->dispatchNotFound();
    }
}
