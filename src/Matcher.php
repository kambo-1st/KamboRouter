<?php

namespace Kambo\Router;

// \spl
use InvalidArgumentException;

// \Psr\Http\Message
use Psr\Http\Message\ServerRequestInterface as ServerRequest;

// \Kambo\Router\
use Kambo\Router\Dispatchers\Interfaces\DispatcherInterface;
use Kambo\Router\Route\Collection;
use Kambo\Router\Route\ParsedRoute;

// \Kambo\Router\Enum
use Kambo\Router\Enum\Method;
use Kambo\Router\Enum\RouteMode;

/**
 * Match provided request object with all defined routes in route collection.
 * If some of routes match a data in provided request An instace of matched
 * route is returned. If nothing is matched false value is returned.
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
     * Constructor
     *
     * @param \Kambo\Router\Route\Collection                           $routeCollection
     * @param \Kambo\Router\Dispatchers\Interfaces\DispatcherInterface $dispatcher
     *
     */
    public function __construct(
        Collection $routeCollection
    ) {
        $this->routeCollection = $routeCollection;
    }

    /**
     * Match request with provided routes.
     * Get method and url from provided request and start matching.
     *
     * @param ServerRequest $request instance of PSR 7 compatible request object
     *
     * @return mixed
     */
    public function matchRequest(/*ServerRequest */ $request)
    {
        return $this->getMatchRoute(
            $request->getMethod(),
            $this->getUrl($request)
        );
    }

    /**
     * Match url and method with provided routes.
     *
     * @param string $method http method
     * @param string $url    url
     *
     * @return mixed
     */
    public function matchPathAndMethod($method, $url)
    {
        return $this->getMatchRoute($method, $url);
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
    private function getMatchRoute($method, $url)
    {
        $parsedRoutes = $this->parseRoutes($this->routeCollection);
        foreach ($parsedRoutes as $singleParsedRoute) {
            list($routeRegex, $route) = $singleParsedRoute;
            $matchedParameters = $this->routeMatch($routeRegex, $url);
            if ($matchedParameters !== false) {
                $routeMethod = $route->getMethod();
                if ($routeMethod === $method || $routeMethod === Method::ANY) {
                    $route->setParameters($matchedParameters);

                    return $route;
                }
            }
        }

        return false;
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
     * Match route by provided regex.
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
    private function parseRoutes(Collection $routes)
    {
        $parsedRoutes = [];
        foreach ($routes as $route) {
            $routeUrl = strtr($route->getUrl(), $this->regexShortcuts);

            list($routeRegex, $parameters) = $this->transformRoute($routeUrl);

            $parsedRoute = new ParsedRoute($route);
            $parsedRoute->setPlaceholders($parameters);

            $parsedRoutes[] = [$routeRegex, $parsedRoute];
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
                list($valueToReplace, , $parametersVariables) = array_pad(
                    $variables,
                    3,
                    null
                );
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
