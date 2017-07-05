<?php
namespace Kambo\Tests\Router\Route;

use PHPUnit\Framework\TestCase;

use Kambo\Router\Route\Route\Base;
use Kambo\Router\Route\Route\Parsed;
use Kambo\Router\Enum\Method;

use Kambo\Router\Route\Route;

/**
 * Test for ParsedRoute class
 *
 * @package Kambo\Tests\Router\Route
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license MIT
 */
class ParsedTest extends TestCase
{
    /**
     * Test get and set route placeholders
     *
     * @return void
     */
    public function testSetGetPlaceholders()
    {
        $baseRoute = $this->createMock(Route::class);

        $parsedRoute = new Parsed($baseRoute);
        $parsedRoute->setPlaceholders(['foo' => 'bar']);

        $this->assertEquals(['foo' => 'bar'], $parsedRoute->getPlaceholders());
    }

    /**
     * Test get and set route parameters
     *
     * @return void
     */
    public function testSetGetParameters()
    {
        $baseRoute = $this->createMock(Route::class);

        $parsedRoute = new Parsed($baseRoute);
        $parsedRoute->setParameters(['foo' => 'bar']);

        $this->assertEquals(['foo' => 'bar'], $parsedRoute->getParameters());
    }

    /**
     * Test calling method of the parent
     *
     * @return void
     */
    public function testCallParentMethod()
    {
        $parsedRoute = new Parsed($this->getRouteStub());

        $this->assertTrue($parsedRoute->baseMethod());
    }

    // ------------ PRIVATE METHODS

    /**
     * Get Route stub
     *
     * @return Route
     */
    private function getRouteStub() : Route
    {
        return new class() implements Route {

            private $method;
            private $url;
            private $handler;

            /**
             * Sets route method
             *
             * @param string $method route method
             *
             * @return self for fluent interface
             */
            public function setMethod($method)
            {
                $this->method = $method;
            }

            /**
             * Get route method
             *
             * @return string
             */
            public function getMethod()
            {
            }

            /**
             * Sets URL for route
             *
             * @param mixed $url route url
             *
             * @return self for fluent interface
             */
            public function setUrl($url)
            {
                $this->url = $url;
            }

            /**
             * Get URL of route
             *
             * @return string
             */
            public function getUrl()
            {
            }

            /**
             * Sets handler that will be executed if the url will match the route.
             *
             * @param mixed $handler handler which will be executed if the url will
             *                       match the route
             *
             * @return self for fluent interface
             */
            public function setHandler($handler)
            {
                $this->handler = $handler;
            }

            /**
             * Get handler
             *
             * @return mixed
             */
            public function getHandler()
            {
            }

            /**
             * Base method for testing
             *
             * @return mixed
             */
            public function baseMethod()
            {
                return true;
            }
        };
    }
}
