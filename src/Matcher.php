<?php

namespace Kambo\Router;

// \spl
use InvalidArgumentException;

// \Kambo\Router\
use Kambo\Router\Dispatchers\Interfaces\DispatcherInterface;
use Kambo\Router\Route\Collection;

// \Kambo\Router\Enum
use Kambo\Router\Enum\Method;
use Kambo\Router\Enum\RouteMode;

/**
 * Match provided request object with all defined routes in route collection.
 * If some of routes match a data in provided request. Route is dispatched
 * with additionall parameters. If nothing is matched execution is passed to
 * specific function in dispatcher
 *
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license Apache-2.0
 * @package Kambo\Router
 */
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
    private $regexShortcuts = [
        ':i}'  => ':[0-9]+}',
        ':a}'  => ':[0-9A-Za-z]+}',
        ':h}'  => ':[0-9A-Fa-f]+}',
        ':c}'  => ':[a-zA-Z0-9+_\-\.]+}'
    ];

    /**
     * Flag for enabling support of mode rewrite.
     *
     * @var string
     */
    private $urlFormat = RouteMode::PATH_FORMAT;

    /**
     * Name of GET parameter from which the route will be get
     * if url format is set to GET_FORMAT.
     *
     * @var string
     */
    private $modeRewriteParameter = 'r';

    /**
     * Instance of route collection
     *
     * @var \Kambo\Router\Route\Collection
     */
    private $routeCollection;

    /**
     * Instance of Dispatcher which will dispatch the request
     *
     * @var \Kambo\Router\Dispatchers\Interfaces\DispatcherInterface
     */
    private $dispatcher;

    /**
     * Constructor
     *
     * @param \Kambo\Router\Route\Collection                           $routeCollection
     * @param \Kambo\Router\Dispatchers\Interfaces\DispatcherInterface $dispatcher
     *
     */
    public function __construct(Collection $routeCollection, DispatcherInterface $dispatcher)
    {
        $this->routeCollection = $routeCollection;
        $this->dispatcher      = $dispatcher;
    }

    /**
     * Match request with provided routes.
     * Get method and url from provided request and start matching.
     *
     * @param object $request instance of PSR 7 compatible request object
     *
     * @return mixed
     */
    public function match($request)
    {
        return $this->matchRoute(
            $request->getMethod(),
            $this->getUrl($request)
        );
    }

    /**
     * Match method and route with provided routes.
     * If route and method match a route is dispatch using provided dispatcher.
     *
     * @param string $method http method
     * @param string $url    url
     *
     * @return mixed
     */
    public function matchRoute($method, $url)
    {
        $parsedRoutes = $this->parseRoutes($this->routeCollection->getRoutes());
        foreach ($parsedRoutes as $parameters) {
            $matchedRouted = $this->routeMatch($parameters->getParsed(), $url);
            if ($matchedRouted !== false) {
                $routeMethod = $parameters->getMethod();
                if ($routeMethod === $method || $routeMethod === Method::ANY) {
                    return $this->dispatcher->dispatchRoute(
                        $parameters,
                        $matchedRouted
                    );
                }
            }
        }

        return $this->dispatcher->dispatchNotFound();
    }

    /**
     * Set format for URL resolving.
     * If the path mode is set to a path a web server must be properly
     * configurated, defualt value is PATH_FORMAT.
     *
     * @param string $urlFormat value from RouteMode enum
     *
     * @return self for fluent interface
     */
    public function setUrlFormat($urlFormat)
    {
        if (RouteMode::inEnum($urlFormat)) {
            $this->urlFormat = $urlFormat;
        } else {
            throw new InvalidArgumentException(
                'Value of urlFormat must be from RouteMode enum.'
            );
        }

        return $this;
    }

    /**
     * Get format for URL resolving.
     *
     * @return string value from RouteMode enum
     */
    public function getUrlFormat()
    {
        return $this->urlFormat;
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
    private function routeMatch($routeRegex, $route)
    {
        $matches = [];
        if (preg_match($routeRegex, $route, $matches)) {
            unset($matches[0]);

            return array_values($matches);
        }

        return false;
    }

    /**
     * Prepare regex and parameters for each of routes.
     *
     * @param array $routes array with instances of route object
     *
     * @return array transformed routes
     */
    private function parseRoutes($routes)
    {
        foreach ($routes as $route) {
            $routeUrl = strtr($route->getUrl(), $this->regexShortcuts);

            list($routeRegex, $parameters) = $this->transformRoute($routeUrl);
            $route->setParsed($routeRegex)->setParameters($parameters);
        }

        return $routes;
    }

    /**
     * Get route from request object.
     * Method expect an instance of PSR 7 compatible request object.
     *
     * @param object $request
     *
     * @return string
     */
    private function getUrl($request)
    {
        if ($this->urlFormat === RouteMode::PATH_FORMAT) {
            $path = $request->getUri()->getPath();
        } else {
            $queryParams = $request->getQueryParams();
            $route       = null;
            if (isset($queryParams[$this->modeRewriteParameter])) {
                $route = $queryParams[$this->modeRewriteParameter];
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
    private function transformRoute($route)
    {
        $parameters = $this->extractVariableRouteParts($route);
        if (isset($parameters)) {
            foreach ($parameters as $variables) {
                list($valueToReplace, , $parametersVariables) = array_pad($variables, 3, null);
                if (isset($parametersVariables)) {
                    $route = str_replace(
                        $valueToReplace,
                        '('.reset($parametersVariables).')',
                        $route
                    );
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
     * @return null|array
     */
    private function extractVariableRouteParts($route)
    {
        $matches = null;
        preg_match_all(
            self::VARIABLE_REGEX,
            $route,
            $matches,
            PREG_OFFSET_CAPTURE | PREG_SET_ORDER
        );

        return $matches;
    }
}
