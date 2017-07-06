<?php
namespace Kambo\Tests\Router\Route;

use PHPUnit\Framework\TestCase;

use Kambo\Router\Route\Route\Base;
use Kambo\Router\Enum\Method;

/**
 * Test for Route class
 *
 * @package Kambo\Tests\Router\Route
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license MIT
 */
class BaseTest extends TestCase
{
    /**
     * Test get and set route method
     *
     * @return void
     */
    public function testGetSetMethod()
    {
        $testRoute = new Base(
            Method::POST,
            'foo',
            function () {
            }
        );

        $this->assertEquals(Method::POST, $testRoute->getMethod());

        $testRoute->setMethod(Method::GET);

        $this->assertEquals(Method::GET, $testRoute->getMethod());
    }

    /**
     * Test get and set route url
     *
     * @return void
     */
    public function testGetSetUrl()
    {
        $testRoute = new Base(
            Method::POST,
            'foo',
            function () {
            }
        );

        $this->assertEquals('foo', $testRoute->getUrl());

        $testRoute->setUrl('bar');

        $this->assertEquals('bar', $testRoute->getUrl());
    }

    /**
     * Test get and set route handler
     *
     * @return void
     */
    public function testGetSetHandler()
    {
        $testRoute = new Base(
            Method::POST,
            'foo',
            function () {
            }
        );

        $this->assertTrue($this->isClosure($testRoute->getHandler()));

        $testRoute->setHandler(
            function () {
            }
        );

        $this->assertTrue($this->isClosure($testRoute->getHandler()));
    }

    // ------------ PRIVATE METHODS

    /**
     * Check if variable is closure
     *
     * @param mixed $type variable to check
     *
     * @return boolean return true if is
     */
    private function isClosure($type)
    {
        return is_object($type) && ($type instanceof \Closure);
    }
}
