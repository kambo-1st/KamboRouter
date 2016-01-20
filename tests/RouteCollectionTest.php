<?php
namespace Test;

use Kambo\Router\Route\RouteCollection;
use Kambo\Router\Enum\Method;
use Kambo\Router\Route\Route;

class RouteCollectionTest extends \PHPUnit_Framework_TestCase {

    /**
     * Test get method
     * 
     * @return void
     */
    public function testGet() {
        $testInstance = new RouteCollection();
        $testInstance->get('test.com/test', function(){});

        $definedRoute = $testInstance->getRoutes()[0];
        $this->assertEquals(Method::GET, $definedRoute->getMethod());
        $this->assertEquals('test.com/test', $definedRoute->getUrl());
        $this->assertTrue($this->_isClosure($definedRoute->getHandler()));
    }

    /**
     * Test post method
     * 
     * @return void
     */
    public function testPost() {
        $testInstance = new RouteCollection();
        $testInstance->post('test.com/test', function(){});

        $definedRoute = $testInstance->getRoutes()[0];
        $this->assertEquals(Method::POST, $definedRoute->getMethod());
        $this->assertEquals('test.com/test', $definedRoute->getUrl());
        $this->assertTrue($this->_isClosure($definedRoute->getHandler()));
    }

    /**
     * Test delete method
     * 
     * @return void
     */
    public function testDelete() {
        $testInstance = new RouteCollection();
        $testInstance->delete('test.com/test', function(){});

        $definedRoute = $testInstance->getRoutes()[0];
        $this->assertEquals(Method::DELETE, $definedRoute->getMethod());
        $this->assertEquals('test.com/test', $definedRoute->getUrl());
        $this->assertTrue($this->_isClosure($definedRoute->getHandler()));
    }

    /**
     * Test put method
     * 
     * @return void
     */
    public function testPut() {
        $testInstance = new RouteCollection();
        $testInstance->put('test.com/test', function(){});

        $definedRoute = $testInstance->getRoutes()[0];
        $this->assertEquals(Method::PUT, $definedRoute->getMethod());
        $this->assertEquals('test.com/test', $definedRoute->getUrl());
        $this->assertTrue($this->_isClosure($definedRoute->getHandler()));
    }

    /**
     * Test any method
     * 
     * @return void
     */
    public function testAny() {
        $testInstance = new RouteCollection();
        $testInstance->any('test.com/test', function(){});

        $definedRoute = $testInstance->getRoutes()[0];
        $this->assertEquals(Method::ANY, $definedRoute->getMethod());
        $this->assertEquals('test.com/test', $definedRoute->getUrl());
        $this->assertTrue($this->_isClosure($definedRoute->getHandler()));
    }

    /**
     * Test create method
     * 
     * @return void
     */
    public function testCreateRoute() {
        $testInstance = new RouteCollection();
        $testInstance->createRoute(Method::ANY,'test.com/any', function(){});
        $testInstance->createRoute(Method::POST,'test.com/post', function(){});
        $testInstance->createRoute(Method::GET,'test.com/get', function(){});

        $definedRoutes = $testInstance->getRoutes();
        list($routeAny, $routePost, $routeGet) = $testInstance->getRoutes();   

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
    public function testAddRoute() {
        $testInstance = new RouteCollection();

        $route = new Route(Method::ANY,'test.com/any', function(){});
        $testInstance->addRoute($route);

        $route = new Route(Method::POST,'test.com/post', function(){});
        $testInstance->addRoute($route);

        $route = new Route(Method::GET,'test.com/get', function(){});
        $testInstance->addRoute($route);

        $definedRoutes = $testInstance->getRoutes();
        list($routeAny, $routePost, $routeGet) = $testInstance->getRoutes();   

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
    private function _isClosure($type) {
        return is_object($type) && ($type instanceof \Closure);
    }  

}