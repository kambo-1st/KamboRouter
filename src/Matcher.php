<?php

namespace Kambo\Router;

/**
 * XXX
 *
 * @author   Bohuslav Simek <bohuslav@simek.si>
 * @version  GIT $Id$
 * @license  Apache-2.0
 * @category Router
 * @package  Router
 * 
 */

use Kambo\Router\Enum\Methods;
use Kambo\Router\Interfaces\DispatcherInterface;

class Matcher 
{
    CONST VARIABLE_REGEX = 
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

    private $_modeRewrite = true;

    private $_modeRewriteParameter = 'r';

    private $_routeCollection;

    private $_dispatcher;

    private $_routes = [];

    function __construct($routeCollection, $dispatcher) {
        $this->_routeCollection = $routeCollection;
        $this->_dispatcher      = $dispatcher;
    }

    public function match($request) {
        return $this->matchRoute($request->getMethod(), $this->_getRoute($request));
    }

    public function matchRoute($method, $route) {
        $parsedRoutes = $this->_parseRoutes($this->_routeCollection->getRoutes());
        foreach ($parsedRoutes as $parameters) {
            if (preg_match($parameters['routeRegex'], $route, $matches)) {
                if ($parameters['method'] === $method || $parameters['method'] === Methods::ANY) {
                    unset($matches[0]);
                    return $this->_dispatcher->dispatchRoute($parameters, $matches);
                }
            }
        }
     
        return $this->_dispatcher->dispatchNotFound();
    }  

    public function setUseModeRewrite($useModeRewrite) {
        $this->_modeRewrite = $useModeRewrite;   
        return $this;
    }

    public function getUseModeRewrite() {
        return $this->_modeRewrite;
    }

    // ------------ PRIVATE FUNCTIONS 
    
    private function _parseRoutes($routes) {
        $parsedRoutes = [];
        foreach ($routes as $possition => $route) {
            $routeNew = strtr($route['route'], $this->_regexShortcuts);
            list($route['routeRegex'], $route['parameters']) = $this->_transformRoute($routeNew);
            $parsedRoutes[] = $route;         
        }

        return $parsedRoutes;
    }

    private function _getRoute($request) {
        if ($this->_modeRewrite) {
            $path = $request->getUri()->getPath();
        } else {
            $queryParams = $request->getQueryParams();
            $route = null;
            if (isset($queryParams[$this->_modeRewriteParameter])) {
                $route = $queryParams[$this->_modeRewriteParameter];
            }

            $path = '/'.$route;
        }

        return $path;
    }

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

    private function _extractVariableRouteParts($route) {
        if (
            preg_match_all(self::VARIABLE_REGEX, $route, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER)
        ) {
            return $matches;
        }
    }                 
}