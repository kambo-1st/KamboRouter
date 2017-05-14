<?php
namespace Kambo\Router\Dispatchers\Interfaces;

use Kambo\Router\Route\ParsedRoute;

/**
 * Interface for dispatcher
 *
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license Apache-2.0
 * @package Kambo\Router\Dispatchers\Interfaces
 */
interface DispatcherInterface
{
    /**
     * Dispatch found route with given parameters
     *
     * @param ParsedRoute $route Instance of found parsed route
     *
     * @return mixed
     */
    public function dispatchRoute(ParsedRoute $route);

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
