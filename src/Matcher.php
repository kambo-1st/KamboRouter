<?php
declare(strict_types=1);

namespace Kambo\Router;

// \Psr\Http\Message
use Psr\Http\Message\ServerRequestInterface as ServerRequest;

/**
 * Match provided request object with all defined routes in route collection.
 * If some of routes match a data in provided request An instance of matched
 * parsed route is returned. If nothing is matched false value is returned.
 *
 * @package Kambo\Router
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license MIT
 */
interface Matcher
{
    /**
     * Match request with provided routes.
     * Get method and url from provided request and start matching.
     *
     * @param ServerRequest $request instance of PSR 7 compatible request object
     *
     * @return mixed
     */
    public function matchRequest(ServerRequest $request);

    /**
     * Match url and method with provided routes.
     *
     * @param string $method http method
     * @param string $url    url
     *
     * @return mixed
     */
    public function matchPathAndMethod(string $method, string $url);
}
