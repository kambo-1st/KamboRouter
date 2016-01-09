<?php

namespace Kambo\Router;

/**
 * Match provided request object with all defined routes in route collection.
 * If some of routes match a data in provided request. Route is dispatched 
 * with additionall parameters. If nothing is matched execution is passed to 
 * specific function in dispatcher
 *
 * @author   Bohuslav Simek <bohuslav@simek.si>
 * @version  GIT $Id$
 * @license  Apache-2.0
 * @category Router
 * @package  Router
 * 
 */

use Kambo\Router\Enum\Method;
use Kambo\Router\Interfaces\DispatcherInterface;
use Kambo\Router\RouteCollection;

class Matcher 
{
    /**
     * Regex for getting URL variables
     *
     * @var string
     */    
    const VARIABLE_REGEX = 
        "~\{
            \s* ([a-zA-Z0-9_]*) \s*
            (?:
                : \s* ([^{]+(?:\{.*?\})?)
            )?
        \}\??~x";

    /**
     * Shortcuts for regex.
     *
     * @var array
     */
    private $_regexShortcuts = array(
        ':i}'  => ':[0-9]+}',
        ':a}'  => ':[0-9A-Za-z]+}',
        ':h}'  => ':[0-9A-Fa-f]+}',
        ':c}'  => ':[a-zA-Z0-9+_\-\.]+}'
    );

    /**
     * Flag for enabling support of mode rewrite.
     *
     * @var boolean
     */  
    private $_modeRewrite = true;

    /**
     * Name of GET parameter from which the route will be get 
     * if flag of modeRewrite is set as false.
     *
     * @var string
     */  
    private $_modeRewriteParameter = 'r';

    /**
     * Instance of route collection 
     *
     * @var Kambo\Router\RouteCollection
     */  
    private $_routeCollection;

    /**
     * Instance of Dispatcher which will dispatch the request
     *
     * @var Kambo\Router\Interfaces\DispatcherInterface
     */  
    private $_dispatcher;

    /**
     * Object constructor for injecting depedancies
     *
     * @param RouteCollection     $routeCollection 
     * @param DispatcherInterface $dispatcher 
     *
     */
    public function __construct(RouteCollection $routeCollection, DispatcherInterface $dispatcher) {
        $this->_routeCollection = $routeCollection;
        $this->_dispatcher      = $dispatcher;
    }

    /**
     * Match request with provided routes.
     * Get method and url from provided request and start matching.
     *
     * @param object $request instance of PSR 7 compatible request object
     * 
     * @return mixed
     */
    public function match($request) {
        return $this->matchRoute($request->getMethod(), $this->_getRoute($request));
    }

    /**
     * Match method and route with provided routes.
     * If route and method match a route is dispatch using provided dispatcher. 
     *
     * @param string $method http method
     * @param string $route  url
     *
     * @return mixed
     */
    public function matchRoute($method, $route) {
        $parsedRoutes = $this->_parseRoutes($this->_routeCollection->getRoutes());
        foreach ($parsedRoutes as $parameters) {
            $matches = $this->_routeMatch($parameters['routeRegex'], $route);
            if ($matches !== false) {
                if ($parameters['method'] === $method || $parameters['method'] === Method::ANY) {
                    return $this->_dispatcher->dispatchRoute($parameters, $matches);
                }
            }
        }

        return $this->_dispatcher->dispatchNotFound();
    }  

    /**
     * Enable/disable usage of mode rewrite.
     * Set to false for disabling mode rewrite, defualt value is true.
     *
     * @param boolean $useModeRewrite
     *
     * @return self for fluent interface
     */
    public function setUseModeRewrite($useModeRewrite) {
        $this->_modeRewrite = $useModeRewrite;   
        return $this;
    }

    /**
     * Get state of mode rewrite.
     *
     * @return boolean
     */
    public function getUseModeRewrite() {
        return $this->_modeRewrite;
    }

    // ------------ PRIVATE METHODS

    /**
     * Match route with provideed regex.
     *
     * @param string $routeRegex
     * @param string $route
     * 
     * @return mixed
     */    
    private function _routeMatch($routeRegex, $route) {
        $matches = [];
        if (preg_match($routeRegex, $route, $matches)) {
            unset($matches[0]);
            return $matches;
        }  

        return false;      
    }

    /**
     * Prepare regex and parameters for each of routes.
     *
     * @param string $routes
     * 
     * @return array transformed routes
     */    
    private function _parseRoutes($routes) {
        $parsedRoutes = [];
        foreach ($routes as $possition => $route) {
            $routeNew = strtr($route['route'], $this->_regexShortcuts);
            list($route['routeRegex'], $route['parameters']) = $this->_transformRoute($routeNew);
            $parsedRoutes[] = $route;         
        }

        return $parsedRoutes;
    }

    /**
     * Get route from request object.
     * Method expect an instance of PSR 7 compatible request object.
     *
     * @param object $request
     * 
     * @return string
     */   
    private function _getRoute($request) {
        if ($this->_modeRewrite) {
            $path = $request->getUri()->getPath();
        } else {
            $queryParams = $request->getQueryParams();
            $route       = null;
            if (isset($queryParams[$this->_modeRewriteParameter])) {
                $route = $queryParams[$this->_modeRewriteParameter];
            }

            $path = '/'.$route;
        }

        return $path;
    }

    /**
     * Prepare regex for resolving route a extract variables from route. 
     *
     * @param string $route
     * 
     * @return array regex and parameters
     */   
    private function _transformRoute($route) {
        $parameters = $this->_extractVariableRouteParts($route);
        if (isset($parameters)) {
            foreach ($parameters as $variables) {
                list($valueToReplace, $valueName, $parametersVariables)
                    = array_pad($variables, 3, null);
                if (isset($parametersVariables)) {
                    $route = str_replace($valueToReplace, '('.reset($parametersVariables).')', $route);
                } else {
                    $route = str_replace($valueToReplace, '([^/]+)', $route);
                }
            }
        }

        $route = '~^'.$route.'$~';

        return [$route, $parameters];
    }     

    /**
     * Extract variables from route 
     *
     * @param string $route
     *
     * @return array
     */
    private function _extractVariableRouteParts($route) {
        $matches = null;
        preg_match_all(self::VARIABLE_REGEX, $route, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);

        return $matches;
    }                 
}