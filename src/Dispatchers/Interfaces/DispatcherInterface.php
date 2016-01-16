<?php
namespace Kambo\Router\Dispatchers\Interfaces;

use Kambo\Router\Route\Route;

/**
 * Interface for dispatcher
 *
 * @author   Bohuslav Simek <bohuslav@simek.si>
 * @version  GIT $Id$
 * @license  Apache-2.0
 * @category Dispatchers
 * @package  Router
 * 
 */
interface DispatcherInterface 
{
    /**
     * Dispatch found route with given parameters
     * 
     * @param mixed $route      found route
     * @param mixed $parameters parameters for route
     *
     * @return mixed
     */    
    public function dispatchRoute(Route $route, array $parameters);

    /**
     * Called if nothing was not found.
     * Can call a a defined handler or raise exception if the handler will not be specified.
     * 
     * @return mixed
     */    
    public function dispatchNotFound();

    /**
     * Set not found handler
     * 
     * @return self for fluent interface
     */    
    public function setNotFoundHandler($handler);
}