<?php
namespace Kambo\Tests\Router\Matcher;

use PHPUnit\Framework\TestCase;

// \Kambo\Router\Route
use Kambo\Router\Route\Collection;
use Kambo\Router\Route\Builder\Base;
use Kambo\Router\Route\Route\Parsed;

// \Kambo\Router\Dispatcher
use Kambo\Router\Dispatcher\ClosureAutoBind;
use Kambo\Router\Dispatcher\ClassAutoBind;

// \Kambo\Router\Matcher
use Kambo\Router\Matcher\Regex;

use Kambo\Router\Enum\Method;
use Kambo\Router\Enum\RouteMode;

use Kambo\Tests\Router\Request\Enviroment;
use Kambo\Tests\Router\Request\Request;

/**
 * Tests for Regex class
 *
 * @package Kambo\Tests\Router\Matcher
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license MIT
 */
class RegexTest extends TestCase
{
    /**
     * Tests execution of closure with static route.
     *
     * @return void
     */
    public function testAnonymousFunctionStatic()
    {
        $routeCollection = new Collection(new Base());
        $routeCollection->get(
            '/homepage/',
            function () {
                return 'executed';
            }
        );

        $matcher      = new Regex($routeCollection);
        $matchedRoute = $matcher->matchPathAndMethod(Method::GET, '/homepage/');

        $this->assertInstanceOf(Parsed::class, $matchedRoute);
    }

    /**
     * Tests execution of closure with static route for GET and POST method.
     *
     * @return void
     */
    public function testAnonymousFunctionStaticGetPost()
    {
        $routeCollection = new Collection(new Base());
        $routeCollection->get(
            '/homepage/',
            function () {
                return 'get';
            }
        );
        $routeCollection->post(
            '/homepage/',
            function () {
                return 'post';
            }
        );

        $matcher      = new Regex($routeCollection);
        $matchedRoute = $matcher->matchPathAndMethod(Method::POST, '/homepage/');

        $this->assertInstanceOf(Parsed::class, $matchedRoute);
        $this->assertEquals('POST', $matchedRoute->getMethod());
    }

    /**
     * Tests execution of closure for any method.
     *
     * @return void
     */
    public function testAnonymousFunctionStaticAny()
    {
        $routeCollection = new Collection(new Base());
        $routeCollection->any(
            '/homepage/',
            function () {
                return 'any';
            }
        );

        $matcher      = new Regex($routeCollection);
        $matchedRoute = $matcher->matchPathAndMethod(Method::POST, '/homepage/');

        $this->assertInstanceOf(Parsed::class, $matchedRoute);
        $this->assertEquals('ANY', $matchedRoute->getMethod());
    }

    /**
     * Tests execution of closure for not found handler.
     *
     * @return void
     */
    public function testRouteNotFoundAnonymousFunction()
    {
        $routeCollection = new Collection(new Base());
        $routeCollection->get('/homepage/', function () {
            return 'executed';
        });

        $matcher = new Regex($routeCollection);
        $result  = $matcher->matchPathAndMethod(Method::GET, '/iamnotset/');

        $this->assertFalse($result);
    }

    /**
     * Tests execution of not found handler.
     *
     * @return void
     */
    public function testRouteNotFoundControlerHandler()
    {
        $routeCollection = new Collection(new Base());

        $matcher = new Regex($routeCollection);
        $result  = $matcher->matchPathAndMethod(Method::GET, '/iamnotset/');

        $this->assertFalse($result);
    }

    /**
     * Test anonymous function with single parameter
     *
     * @return void
     */
    public function testAnonymousFunctionSingleParameter()
    {
        $routeCollection = new Collection(new Base());
        $routeCollection->get('/article/{id:\d+}', function ($id) {
            return $id;
        });

        $matcher      = new Regex($routeCollection, new ClosureAutoBind());
        $matchedRoute = $matcher->matchPathAndMethod(Method::GET, '/article/123');

        $this->assertInstanceOf(Parsed::class, $matchedRoute);
        $this->assertEquals(['123'], $matchedRoute->getParameters());
    }

    /**
     * Test anonymous function multiple parameters
     *
     * @return void
     */
    public function testAnonymousFunctionMultipleParameters()
    {
        $routeCollection = new Collection(new Base());

        $routeCollection->get('/user/{name}/{id:\d+}', function () {
        });

        $matcher      = new Regex($routeCollection);
        $matchedRoute = $matcher->matchPathAndMethod(Method::GET, '/user/bohuslav/123');

        $this->assertInstanceOf(Parsed::class, $matchedRoute);
        $this->assertEquals(['bohuslav','123'], $matchedRoute->getParameters());
    }

    /**
     * Test anonymous function multiple with some parameters missing
     *
     * @return void
     */
    public function testAnonymousFunctionMultipleParametersSomeMissing()
    {
        $routeCollection = new Collection(new Base());

        $routeCollection->get('/user/{name}/{id:\d+}', function ($id, $name) {
            $this->assertEquals(123, $id);
            $this->assertEquals('bohuslav', $name);
        });

        $dispatcherClosure = new ClosureAutoBind();
        $dispatcherClosure->setNotFoundHandler(function () {
            return true;
        });

        $matcher = new Regex($routeCollection);
        $result  = $matcher->matchPathAndMethod(Method::GET, '/user/bohuslav');

        $this->assertFalse($result);
    }

    /**
     * Test particular controller and particular action
     *
     * @return void
     */
    public function testParticularControllerParticularAction()
    {
        $routeCollection = new Collection(new Base());

        $routeCollection->get(
            '/video/{id:\d+}',
            [
                'controler'=>'videoControler',
                'action'=>'view'
            ]
        );

        $matcher      = new Regex($routeCollection);
        $matchedRoute = $matcher->matchPathAndMethod(Method::GET, '/video/123');

        $this->assertInstanceOf(Parsed::class, $matchedRoute);
        $this->assertEquals([123], $matchedRoute->getParameters());
    }

    /**
     * Test particular controller dynamic action
     *
     * @return void
     */
    public function testParticularControllerDynamicAction()
    {
        $routeCollection = new Collection(new Base());
        $routeCollection->get(
            '/automatics/video/{action}/{id:\d+}',
            [
                'controler'=>'videoControler',
                'action'=>'{action}'
            ]
        );

        $dispatcher = new ClassAutoBind();
        $dispatcher->setBaseNamespace('Test\Application');

        $matcher      = new Regex($routeCollection, $dispatcher);
        $matchedRoute = $matcher->matchPathAndMethod(Method::GET, '/automatics/video/view/123');

        $this->assertInstanceOf(Parsed::class, $matchedRoute);
        $this->assertEquals(['view', 123], $matchedRoute->getParameters());
    }

    /**
     * Test particular controller dynamic action
     *
     * @return void
     */
    public function testParticularControllerDynamicActionAlternativeOrder()
    {
        $routeCollection = new Collection(new Base());
        $routeCollection->get(
            '/automatics/video/{id:\d+}/{action}',
            [
                'controler'=>'videoControler',
                'action'=>'{action}'
            ]
        );

        $dispatcher = new ClassAutoBind();
        $dispatcher->setBaseNamespace('Test\Application');

        $matcher      = new Regex($routeCollection, $dispatcher);
        $matchedRoute = $matcher->matchPathAndMethod(Method::GET, '/automatics/video/123/view');

        $this->assertInstanceOf(Parsed::class, $matchedRoute);
        $this->assertEquals([123, 'view'], $matchedRoute->getParameters());
    }

    /**
     * Test dynamic controller and dynamic action
     *
     * @return void
     */
    public function testDynamicControllerDynamicAction()
    {
        $routeCollection = new Collection(new Base());
        $routeCollection->get(
            '/automatics/{controler}/{action}/{id:\d+}',
            [
                'controler' => '{controler}',
                'action' => '{action}'
            ]
        );

        $dispatcher = new ClassAutoBind();
        $dispatcher->setBaseNamespace('Test\Application');

        $matcher      = new Regex($routeCollection, $dispatcher);
        $matchedRoute = $matcher->matchPathAndMethod(Method::GET, '/automatics/video/view/123');

        $this->assertInstanceOf(Parsed::class, $matchedRoute);
        $this->assertEquals(['video', 'view', 123], $matchedRoute->getParameters());
    }

    /**
     * Test dynamic controller and dynamic action other order
     *
     * @return void
     */
    public function testDynamicControllerDynamicActionAlternativeOrder()
    {
        $routeCollection = new Collection(new Base());
        $routeCollection->get(
            '/automatics/{id:\d+}/{controler}/{action}',
            [
                'controler' => '{controler}',
                'action' => '{action}'
            ]
        );

        $dispatcher = new ClassAutoBind();
        $dispatcher->setBaseNamespace('Test\Application');

        $matcher      = new Regex($routeCollection, $dispatcher);
        $matchedRoute = $matcher->matchPathAndMethod(Method::GET, '/automatics/123/video/view');

        $this->assertInstanceOf(Parsed::class, $matchedRoute);
        $this->assertEquals([123, 'video', 'view'], $matchedRoute->getParameters());
    }

    /**
     * Test dynamic controller, dynamic action in module
     *
     * @return void
     */
    public function testDynamicControllerDynamicActionStaticModule()
    {
        $routeCollection = new Collection(new Base());
        $routeCollection->get(
            '/test/{controler}/{action}/{id:\d+}',
            [
                'module' => 'TestModule',
                'controler' => '{controler}',
                'action' => '{action}'
            ]
        );

        $dispatcher = new ClassAutoBind();
        $dispatcher->setBaseNamespace('Test\Application');

        $matcher      = new Regex($routeCollection, $dispatcher);
        $matchedRoute = $matcher->matchPathAndMethod(Method::GET, '/test/test/view/123');

        $this->assertInstanceOf(Parsed::class, $matchedRoute);
        $this->assertEquals(['test', 'view', 123], $matchedRoute->getParameters());
    }

    /**
     * Test dynamic controller, dynamic action in module
     *
     * @return void
     */
    public function testDynamicControllerDynamicActionDynamicModule()
    {
        $routeCollection = new Collection(new Base());
        $routeCollection->get(
            '/{module}/{controler}/{action}/{id:\d+}',
            [
                'module' => '{module}',
                'controler' =>'{controler}',
                'action' =>'{action}'
            ]
        );

        $dispatcher = new ClassAutoBind();
        $dispatcher->setBaseNamespace('Test\Application');

        $matcher      = new Regex($routeCollection, $dispatcher);
        $matchedRoute = $matcher->matchPathAndMethod(Method::GET, '/testModule/test/view/123');

        $this->assertInstanceOf(Parsed::class, $matchedRoute);
        $this->assertEquals(['testModule', 'test', 'view', 123], $matchedRoute->getParameters());
    }

    /**
     * Test dynamic controller, dynamic action in module REQUEST
     *
     * @return void
     */
    public function testDynamicControllerDynamicActionDynamicModuleMatch()
    {
        $routeCollection = new Collection(new Base());
        $routeCollection->get(
            '/{module}/{controler}/{action}/{id:\d+}',
            [
                'module' => '{module}',
                'controler' => '{controler}',
                'action' => '{action}'
            ]
        );

        $dispatcher = new ClassAutoBind();
        $dispatcher->setBaseNamespace('Test\Application');

        $matcher = new Regex($routeCollection, $dispatcher);
        $enviromentData = [
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/testModule/test/view/123',
        ];

        $enviroment   = new Enviroment($enviromentData);
        $matchedRoute = $matcher->matchRequest(new Request($enviroment));

        $this->assertInstanceOf(Parsed::class, $matchedRoute);
        $this->assertEquals(['testModule', 'test', 'view', 123], $matchedRoute->getParameters());
    }

    /**
     * testRouteNotFoundControlerHandlerWrongRoute
     *
     * @return void
     */
    public function testRouteNotFoundControlerHandlerWrongRoute()
    {
        $routeCollection = new Collection(new Base());
        $routeCollection->get(
            '/{module}/{controler}/{action}/{id:\d+}',
            []
        );

        $dispatcher = new ClassAutoBind($routeCollection);

        $matcher      = new Regex($routeCollection, $dispatcher);
        $matchedRoute = $matcher->matchPathAndMethod(Method::GET, '/testModule');

        $this->assertFalse($matchedRoute);
    }

    /**
     * Test dynamic controller, dynamic action in module REQUEST
     *
     * @return void
     */
    public function testWithoutModeRewrite()
    {
        $routeCollection = new Collection(new Base());
        $routeCollection->get(
            '/{module}/{controler}/{action}/{id:\d+}',
            ['module'=> '{module}', 'controler'=>'{controler}', 'action'=>'{action}']
        );

        $dispatcher = new ClassAutoBind();
        $dispatcher->setBaseNamespace('Test\Application');

        $matcher = new Regex($routeCollection, $dispatcher);
        $matcher->setUrlFormat(RouteMode::GET_FORMAT);

        $enviromentData = [
            'QUERY_STRING' => 'r=testModule/test/view/123',
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/',
        ];

        $enviroment = new Enviroment($enviromentData);
        $matchedRoute    = $matcher->matchRequest(new Request($enviroment));

        $this->assertInstanceOf(Parsed::class, $matchedRoute);
        $this->assertEquals(['testModule', 'test', 'view', 123], $matchedRoute->getParameters());
    }

    /**
     * Test dynamic controller, dynamic action in module REQUEST
     *
     * @return void
     */
    public function testWithoutModeRewriteFullUrl()
    {
        $routeCollection = new Collection(new Base());
        $routeCollection->get(
            '/{module}/{controler}/{action}/{id:\d+}',
            ['module'=> '{module}', 'controler'=>'{controler}', 'action'=>'{action}']
        );

        $dispatcher = new ClassAutoBind();
        $dispatcher->setBaseNamespace('Test\Application');

        $matcher = new Regex($routeCollection, $dispatcher);
        $matcher->setUrlFormat(RouteMode::GET_FORMAT);

        $enviromentData = [
            'QUERY_STRING' => 'r=testModule/test/view/123',
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/index.php?r=testModule/test/view/123',
        ];

        $enviroment   = new Enviroment($enviromentData);
        $matchedRoute = $matcher->matchRequest(new Request($enviroment));

        $this->assertInstanceOf(Parsed::class, $matchedRoute);
        $this->assertEquals(['testModule', 'test', 'view', 123], $matchedRoute->getParameters());
    }

    /**
     * Test dynamic controller, dynamic action in module REQUEST
     *
     * @return void
     *
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidUrlFormat()
    {
        $routeCollection = new Collection(new Base());
        $dispatcher      = new ClassAutoBind();
        $dispatcher->setBaseNamespace('Test\Application');

        $matcher = new Regex($routeCollection, $dispatcher);
        $matcher->setUrlFormat('invalid value');
    }

    /**
     * Test dynamic controller, dynamic action in module REQUEST
     *
     * @return void
     */
    public function testGetUrlFormat()
    {
        $routeCollection = new Collection(new Base());
        $dispatcher      = new ClassAutoBind();
        $dispatcher->setBaseNamespace('Test\Application');

        $matcher = new Regex($routeCollection, $dispatcher);
        $matcher->setUrlFormat(RouteMode::GET_FORMAT);

        $this->assertEquals(RouteMode::GET_FORMAT, $matcher->getUrlFormat());
    }
}
