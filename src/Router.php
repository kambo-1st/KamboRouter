<?php
declare(strict_types=1);

namespace Kambo\Router;

// \Psr\Http\Message
use Psr\Http\Message\ServerRequestInterface as ServerRequest;

//  \Kambo\Router
use Kambo\Router\Dispatcher;
use Kambo\Router\Matcher;

/**
 * Match provided request object with all defined routes in route collection.
 * If some of routes match a data in provided request. Route is dispatched
 * with additionall parameters. If nothing is matched execution is passed to
 * specific function in dispatcher
 *
 * @package Kambo\Router
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license MIT
 */
class Router
{
    /**
     * Instance of route collection
     *
     * @var \Kambo\Router\Matcher
     */
    private $matcher;

    /**
     * Instance of Dispatcher which will dispatch the request
     *
     * @var \Kambo\Router\Dispatcher
     */
    private $dispatcher;

    /**
     * Defualt constructor
     *
     * @param \Kambo\Router\Dispatcher $dispatcher
     * @param \Kambo\Router\Matcher    $matcher
     *
     */
    public function __construct(
        Dispatcher $dispatcher,
        Matcher $matcher
    ) {
        $this->dispatcher = $dispatcher;
        $this->matcher    = $matcher;
    }

    /**
     * Match request with provided routes.
     *
     * @param ServerRequest $request    instance of PSR 7 compatible request object
     * @param array         $parameters Additional parameters which will be passed into
     *                                  the dispatcher.
     *
     * @return mixed
     */
    public function dispatch($request, array $parameters)
    {
        $matchedRoute = $this->matcher->matchRequest($request);
        if ($matchedRoute !== false) {
            return $this->dispatcher->dispatchRoute($matchedRoute, $parameters);
        }

        return $this->dispatcher->dispatchNotFound();
    }
}
