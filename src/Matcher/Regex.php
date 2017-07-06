<?php
declare(strict_types=1);

namespace Kambo\Router\Matcher;

// \spl
use InvalidArgumentException;

// \Psr\Http\Message
use Psr\Http\Message\ServerRequestInterface as ServerRequest;

// \Kambo\Router
use Kambo\Router\Matcher;

// \Kambo\Router\Route
use Kambo\Router\Route\Collection;
use Kambo\Router\Route\Route\Parsed;

// \Kambo\Router\Enum
use Kambo\Router\Enum\Method;
use Kambo\Router\Enum\RouteMode;

/**
 * Match provided request object with all defined routes in route collection
 * using regular expresions. If some of routes match a data in provided request
 * An instance of matched route is returned. If nothing is matched false value
 * is returned.
 *
 * @package Kambo\Router\Matcher
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license MIT
 */
class Regex implements Matcher
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
     * @param \Kambo\Router\Route\Collection $routeCollection
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
    public function matchRequest(ServerRequest $request)
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
    public function matchPathAndMethod(string $method, string $url)
    {
        return $this->getMatchRoute($method, $url);
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
    public function setUrlFormat(string $urlFormat) : Matcher
    {
        if (RouteMode::inEnum($urlFormat)) {
            $this->urlFormat = $urlFormat;

            return $this;
        }

        throw new InvalidArgumentException(
            'Value of urlFormat must be from RouteMode enum.'
        );
    }

    /**
     * Get format for URL resolving.
     *
     * @return string value from RouteMode enum
     */
    public function getUrlFormat() : string
    {
        return $this->urlFormat;
    }

    // ------------ PRIVATE METHODS

    /**
     * Match method and route with provided routes.
     * If route and method match a route is dispatch using provided dispatcher.
     *
     * @param string $method http method
     * @param string $url    url
     *
     * @return mixed
     */
    private function getMatchRoute(string $method, string $url)
    {
        $parsedRoutes = $this->parseRoutes($this->routeCollection);
        foreach ($parsedRoutes as $singleParsedRoute) {
            list($routeRegex, $route) = $singleParsedRoute;
            $matchedParameters        = $this->routeMatch($routeRegex, $url);

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
     * Match route by provided regex.
     *
     * @param string $routeRegex
     * @param string $route
     *
     * @return mixed
     */
    private function routeMatch(string $routeRegex, string $route)
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
    private function parseRoutes(Collection $routes) : array
    {
        $parsedRoutes = [];
        foreach ($routes as $route) {
            $routeUrl = strtr($route->getUrl(), $this->regexShortcuts);

            list($routeRegex, $parameters) = $this->transformRoute($routeUrl);

            $parsedRoute = new Parsed($route);
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
    private function getUrl($request) : string
    {
        if ($this->urlFormat === RouteMode::PATH_FORMAT) {
            return $request->getUri()->getPath();
        }

        $queryParams = $request->getQueryParams();
        $route       = '';

        if (isset($queryParams[$this->modeRewriteParameter])) {
            $route = $queryParams[$this->modeRewriteParameter];
        }

        $path = '/'.$route;

        return $path;
    }

    /**
     * Prepare regex for resolving route a extract variables from route.
     *
     * @param string $route
     *
     * @return array regex and parameters
     */
    private function transformRoute(string $route) : array
    {
        $parameters = $this->extractVariableRouteParts($route);
        foreach ($parameters as $variables) {
            $route = $this->transformRouteVariables($route, $variables);
        }

        $route = '~^'.$route.'$~';

        return [$route, $parameters];
    }

    /**
     * Prepare regex for resolving route a extract variables from route.
     *
     * @param string $route     Route
     * @param array  $variables Variables
     *
     * @return string Transformed route
     */
    private function transformRouteVariables(string $route, array $variables) : string
    {
        list($valueToReplace, , $parametersVariables) = array_pad(
            $variables,
            3,
            null
        );

        if (isset($parametersVariables)) {
            return str_replace(
                $valueToReplace,
                '('.reset($parametersVariables).')',
                $route
            );
        }

        return str_replace($valueToReplace, '([^/]+)', $route);
    }

    /**
     * Extract variables from the route
     *
     * @param string $route
     *
     * @return array
     */
    private function extractVariableRouteParts(string $route) : array
    {
        $matches = [];
        preg_match_all(
            self::VARIABLE_REGEX,
            $route,
            $matches,
            PREG_OFFSET_CAPTURE | PREG_SET_ORDER
        );

        return $matches;
    }
}
