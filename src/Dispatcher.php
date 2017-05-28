<?php
namespace Kambo\Router;

use Kambo\Router\Route\Route\Parsed;

/**
 * Interface for dispatcher
 *
 * @package Kambo\Router\Dispatchers\Interfaces
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license MIT
 */
interface Dispatcher
{
    /**
     * Dispatch found route with given parameters
     *
     * @param \Kambo\Router\Route\Route\Parsed $route      Instance of found and parsed route.
     * @param array                            $parameters Additional parameters which will be passed into
     *                                                     the dispatcher.
     *
     * @return mixed
     */
    public function dispatchRoute(Parsed $route, array $parameters);

    /**
     * Called if any of route did not match the request.
     *
     * @return mixed
     */
    public function dispatchNotFound();

    /**
     * Sets not found handler
     *
     * @param ParsedRoute $route Instance of found parsed route
     *
     * @return self for fluent interface
     */
    public function setNotFoundHandler($handler);
}
