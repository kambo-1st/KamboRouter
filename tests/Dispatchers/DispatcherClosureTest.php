<?php
namespace Kambo\Tests\Router\Dispatchers;

use Kambo\Router\Dispatchers\DispatcherClosure;
use Kambo\Router\Route\ParsedRoute;

/**
 * Description of DispatcherClosureTest
 *
 * Lorem ipsum dolor
 *
 * @package Kambo\Tests\Router\Dispatchers
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license MIT
 */
class DispatcherClosureTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test testDispatchNotFound method
     *
     * @return void
     */
    public function testDispatchNotFound()
    {
        $dispatcherClosure = new DispatcherClosure();

        $dispatcherClosure->setNotFoundHandler(
            function () {
                return true;
            }
        );

        $this->assertTrue($dispatcherClosure->dispatchNotFound());
    }

    /**
     * Test setNotFoundHandler method with invalid value
     *
     * @return void
     *
     * @expectedException \InvalidArgumentException
     */
    public function testSetNotFoundHandlerInvalidValue()
    {
        $dispatcherClosure = new DispatcherClosure();

        $dispatcherClosure->setNotFoundHandler(null);
    }

    /**
     * Test testDispatchNotFound method
     *
     * @return void
     */
    public function testDispatchNotFoundNoHandler()
    {
        $dispatcherClosure = new DispatcherClosure();

        $this->assertNull($dispatcherClosure->dispatchNotFound());
    }

    /**
     * Test testDispatchRoute method
     *
     * @return void
     */
    public function testDispatchRoute()
    {
        $dispatcherClosure = new DispatcherClosure();

        $route = $this->getMockBuilder(ParsedRoute::class)
                      ->disableOriginalConstructor()
                      ->getMock();

        $route->method('getPlaceholders')
              ->willReturn([]);
        $route->method('getParameters')
              ->willReturn([]);

        // As PHPUnit is not able to returned non executed callback, it must be
        // packed in other callback.
        $route->method('getHandler')->will(
            $this->returnCallback(
                function () {
                    return function () {
                        return true;
                    };
                }
            )
        );

        $this->assertTrue($dispatcherClosure->dispatchRoute($route));
    }

    /**
     * Test testDispatchRoute method
     *
     * @return void
     */
    public function testDispatchRouteInvalidHandler()
    {
        $dispatcherClosure = new DispatcherClosure();

        $route = $this->getMockBuilder(ParsedRoute::class)
                      ->disableOriginalConstructor()
                      ->getMock();

        $route->method('getPlaceholders')
              ->willReturn([]);
        $route->method('getParameters')
              ->willReturn([]);

        // As PHPUnit is not able to returned non executed callback, it must be
        // packed in other callback.
        $route->method('getHandler')->will(
            $this->returnCallback(
                function () {
                    return null;
                }
            )
        );

        $this->assertNull($dispatcherClosure->dispatchRoute($route));
    }

    /**
     * Test testDispatchRoute method
     *
     * @return void
     */
    public function testDispatchRouteWithParameters()
    {
        $dispatcherClosure = new DispatcherClosure();

        $route = $this->getMockBuilder(ParsedRoute::class)
                      ->disableOriginalConstructor()
                      ->getMock();

        $parameters = [
            [
                [
                    0 => '{name}',
                    1 => 6,
                ],
                [
                    0 => 'foo',
                    1 => 7,
                ],
            ],
            [
                [
                    0 => '{id:\\d+}',
                    1 => 13,
                ],
                [
                    0 => 'bar',
                    1 => 14,
                ],
                [
                    0 => '\\d+',
                    1 => 17,
                ],
            ],
        ];
        $route->method('getPlaceholders')
              ->willReturn($parameters);
        $route->method('getParameters')
              ->willReturn(['foo', 'bar']);

        // As PHPUnit is not able to returned non executed callback, it must be
        // packed in other callback.
        $route->method('getHandler')->will(
            $this->returnCallback(
                function () {
                    return function ($foo, $bar) {
                        return [
                            $foo,
                            $bar
                        ];
                    };
                }
            )
        );

        $expectedValues = ['foo', 'bar'];
        $this->assertEquals(
            $expectedValues,
            $dispatcherClosure->dispatchRoute($route)
        );
    }
}
