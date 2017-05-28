<?php
namespace Kambo\Tests\Router\Dispatcher;

use Kambo\Router\Dispatcher\ClosureAutoBind;
use Kambo\Router\Route\Route\Parsed;

/**
 * Tests for DispatcherClosure class
 *
 * @package Kambo\Tests\Router\Dispatcher
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license Apache-2.0
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
        $dispatcherClosure = new ClosureAutoBind();

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
        $dispatcherClosure = new ClosureAutoBind();

        $dispatcherClosure->setNotFoundHandler(null);
    }

    /**
     * Test testDispatchNotFound method
     *
     * @return void
     */
    public function testDispatchNotFoundNoHandler()
    {
        $dispatcherClosure = new ClosureAutoBind();

        $this->assertNull($dispatcherClosure->dispatchNotFound());
    }

    /**
     * Test testDispatchRoute method
     *
     * @return void
     */
    public function testDispatchRoute()
    {
        $dispatcherClosure = new ClosureAutoBind();

        $route = $this->getMockBuilder(Parsed::class)
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

        $this->assertTrue($dispatcherClosure->dispatchRoute($route, []));
    }

    /**
     * Test testDispatchRoute method
     *
     * @return void
     */
    public function testDispatchRouteInvalidHandler()
    {
        $dispatcherClosure = new ClosureAutoBind();

        $route = $this->getMockBuilder(Parsed::class)
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

        $this->assertNull($dispatcherClosure->dispatchRoute($route, []));
    }

    /**
     * Test testDispatchRoute method
     *
     * @return void
     */
    public function testDispatchRouteWithParameters()
    {
        $dispatcherClosure = new ClosureAutoBind();

        $route = $this->getMockBuilder(Parsed::class)
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
            $dispatcherClosure->dispatchRoute($route, [])
        );
    }
}
