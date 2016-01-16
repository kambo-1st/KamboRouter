<?php

namespace Kambo\Router\Route;

/**
 * Route
 *
 * @author   Bohuslav Simek <bohuslav@simek.si>
 * @version  GIT $Id$
 * @license  Apache-2.0
 * @category Route
 * @package  Router
 * 
 */


class Route
{
    private $_method  = null;
    private $_handler = null;
    private $_url     = null;

    private $_parsedRoute = null;
    private $_parameters  = null;

    /**
     * Object constructor
     *
     * @param String $method  
     * @param String $url     
     * @param Mixed  $handler 
     *
     */
    public function __construct($method, $url, $handler) {
        $this->_method  = $method;
        $this->_handler = $handler;
        $this->_url     = $url;
    }

    /**
     * Set route method
     * 
     * @param string $method route method
     *
     * @return self for fluent interface
     */
    public function setMethod($method) {
        $this->_method = $method;
        return $this;
    }

    /**
     * Get route method
     *
     * @return string
     */
    public function getMethod() {
        return $this->_method;
    }

    /**
     * Set URL for route
     * 
     * @param mixed $url route url
     *
     * @return self for fluent interface
     */
    public function setUrl($url) {
        $this->_url = $url;
        return $this;
    }

    /**
     * Get URL of route
     *
     * @return string
     */
    public function getUrl() {
        return $this->_url;
    }

    /**
     * Set handler that will be executed if the url will match the route.
     * 
     * @param mixed $handler handler that will be executed if the url will match the route
     *
     * @return self for fluent interface
     */
    public function setHandler($handler) {
        $this->_handler = $handler;
        return $this;
    }

    /**
     * Get handler
     *
     * @return string
     */
    public function getHandler() {
        return $this->_handler;
    }

    /**
     * Set parsed route.
     * 
     * @param mixed $parsed
     *
     * @return self for fluent interface
     */
    public function setParsed($parsed) {
        $this->_parsedRoute = $parsed;
        return $this;
    }

    /**
     * Get parsed route.
     *
     * @return string
     */
    public function getParsed() {
        return $this->_parsedRoute;
    }

    /**
     * Set parameters for route.
     * 
     * @param mixed $parameters
     *
     * @return self for fluent interface
     */
    public function setParameters($parameters) {
        $this->_parameters = $parameters;
        return $this;
    }

    /**
     * Get parameters of route.
     *
     * @return array
     */
    public function getParameters() {
        return $this->_parameters;
    }
}