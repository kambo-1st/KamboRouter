<?php
namespace Kambo\Tests\Router\Route;

use Kambo\Router\Route\Builder\Base as BaseBuilder;
use Kambo\Router\Route\Collection;
use Kambo\Router\Route\Route\Base as BaseRouter;

use Kambo\Router\Enum\Method;

/**
 * Test for Collection class
 *
 * @package Test
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license MIT
 */
class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test get method
     *
     * @return void
     */
    public function testGet()
    {
        $testInstance = new Collection(new BaseBuilder());
        $testInstance->get(
            'test.com/test',
            function () {
            }
        );

        $definedRoute = iterator_to_array($testInstance)[0];
        $this->assertEquals(Method::GET, $definedRoute->getMethod());
        $this->assertEquals('test.com/test', $definedRoute->getUrl());
        $this->assertTrue($this->isClosure($definedRoute->getHandler()));
    }

    /**
     * Test post method
     *
     * @return void
     */
    public function testPost()
    {
        $testInstance = new Collection(new BaseBuilder());
        $testInstance->post(
            'test.com/test',
            function () {
            }
        );

        $definedRoute = iterator_to_array($testInstance)[0];
        $this->assertEquals(Method::POST, $definedRoute->getMethod());
        $this->assertEquals('test.com/test', $definedRoute->getUrl());
        $this->assertTrue($this->isClosure($definedRoute->getHandler()));
    }

    /**
     * Test delete method
     *
     * @return void
     */
    public function testDelete()
    {
        $testInstance = new Collection(new BaseBuilder());
        $testInstance->delete(
            'test.com/test',
            function () {
            }
        );

        $definedRoute = iterator_to_array($testInstance)[0];
        $this->assertEquals(Method::DELETE, $definedRoute->getMethod());
        $this->assertEquals('test.com/test', $definedRoute->getUrl());
        $this->assertTrue($this->isClosure($definedRoute->getHandler()));
    }

    /**
     * Test put method
     *
     * @return void
     */
    public function testPut()
    {
        $testInstance = new Collection(new BaseBuilder());
        $testInstance->put(
            'test.com/test',
            function () {
            }
        );

        $definedRoute = iterator_to_array($testInstance)[0];
        $this->assertEquals(Method::PUT, $definedRoute->getMethod());
        $this->assertEquals('test.com/test', $definedRoute->getUrl());
        $this->assertTrue($this->isClosure($definedRoute->getHandler()));
    }

    /**
     * Test any method
     *
     * @return void
     */
    public function testAny()
    {
        $testInstance = new Collection(new BaseBuilder());
        $testInstance->any(
            'test.com/test',
            function () {
            }
        );

        $definedRoute = iterator_to_array($testInstance)[0];
        $this->assertEquals(Method::ANY, $definedRoute->getMethod());
        $this->assertEquals('test.com/test', $definedRoute->getUrl());
        $this->assertTrue($this->isClosure($definedRoute->getHandler()));
    }

    /**
     * Test create method
     *
     * @return void
     */
    public function testCreateRoute()
    {
        $testInstance = new Collection(new BaseBuilder());
        $testInstance->createRoute(
            Method::ANY,
            'test.com/any',
            function () {
            }
        );

        $testInstance->createRoute(
            Method::POST,
            'test.com/post',
            function () {
            }
        );
        $testInstance->createRoute(
            Method::GET,
            'test.com/get',
            function () {
            }
        );

        list($routeAny, $routePost, $routeGet) = iterator_to_array($testInstance);

        $this->assertEquals(Method::ANY, $routeAny->getMethod());
        $this->assertEquals('test.com/any', $routeAny->getUrl());

        $this->assertEquals(Method::POST, $routePost->getMethod());
        $this->assertEquals('test.com/post', $routePost->getUrl());

        $this->assertEquals(Method::GET, $routeGet->getMethod());
        $this->assertEquals('test.com/get', $routeGet->getUrl());
    }

    /**
     * Test addRoute method
     *
     * @return void
     */
    public function testAddRoute()
    {
        $testInstance = new Collection(new BaseBuilder());

        $route = new BaseRouter(
            Method::ANY,
            'test.com/any',
            function () {
            }
        );
        $testInstance->addRoute($route);

        $route = new BaseRouter(
            Method::POST,
            'test.com/post',
            function () {
            }
        );
        $testInstance->addRoute($route);

        $route = new BaseRouter(
            Method::GET,
            'test.com/get',
            function () {
            }
        );
        $testInstance->addRoute($route);

        //$definedRoutes = $testInstance->getRoutes();
        list($routeAny, $routePost, $routeGet) = iterator_to_array($testInstance);

        $this->assertEquals(Method::ANY, $routeAny->getMethod());
        $this->assertEquals('test.com/any', $routeAny->getUrl());

        $this->assertEquals(Method::POST, $routePost->getMethod());
        $this->assertEquals('test.com/post', $routePost->getUrl());

        $this->assertEquals(Method::GET, $routeGet->getMethod());
        $this->assertEquals('test.com/get', $routeGet->getUrl());
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
