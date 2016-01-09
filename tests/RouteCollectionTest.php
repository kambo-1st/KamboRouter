<?php
namespace Test;

use Kambo\Router\RouteCollection;
use Kambo\Router\Enum\Method;

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
        $this->assertEquals(Method::GET, $definedRoute['method']);
        $this->assertEquals('test.com/test', $definedRoute['route']);
        $this->assertTrue($this->_isClosure($definedRoute['handler']));
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
        $this->assertEquals(Method::POST, $definedRoute['method']);
        $this->assertEquals('test.com/test', $definedRoute['route']);
        $this->assertTrue($this->_isClosure($definedRoute['handler']));
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
        $this->assertEquals(Method::DELETE, $definedRoute['method']);
        $this->assertEquals('test.com/test', $definedRoute['route']);
        $this->assertTrue($this->_isClosure($definedRoute['handler']));
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
        $this->assertEquals(Method::PUT, $definedRoute['method']);
        $this->assertEquals('test.com/test', $definedRoute['route']);
        $this->assertTrue($this->_isClosure($definedRoute['handler']));
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
        $this->assertEquals(Method::ANY, $definedRoute['method']);
        $this->assertEquals('test.com/test', $definedRoute['route']);
        $this->assertTrue($this->_isClosure($definedRoute['handler']));
    }

    /**
     * Test addRoute method
     * 
     * @return void
     */
    public function testAddRoute() {
        $testInstance = new RouteCollection();
        $testInstance->addRoute(Method::ANY,'test.com/any', function(){});
        $testInstance->addRoute(Method::POST,'test.com/post', function(){});
        $testInstance->addRoute(Method::GET,'test.com/get', function(){});

        $definedRoutes = $testInstance->getRoutes();
        list($routeAny, $routePost, $routeGet) = $testInstance->getRoutes();   

        $this->assertEquals(Method::ANY, $routeAny['method']);
        $this->assertEquals('test.com/any', $routeAny['route']);        

        $this->assertEquals(Method::POST, $routePost['method']);
        $this->assertEquals('test.com/post', $routePost['route']);      

        $this->assertEquals(Method::GET, $routeGet['method']);
        $this->assertEquals('test.com/get', $routeGet['route']);              
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