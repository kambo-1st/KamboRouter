<?php
namespace Kambo\Tests\Router;

use Kambo\Router\Route\Collection;
use Kambo\Router\Dispatchers\DispatcherClosure;
use Kambo\Router\Dispatchers\DispatcherClass;
use Kambo\Router\Matcher;

use Kambo\Router\Enum\Method;
use Kambo\Router\Enum\RouteMode;

class MatcherTest extends \PHPUnit_Framework_TestCase
{
    /**
     * testAnonymousFunctionStatic
     *
     * @return void
     */
    public function testAnonymousFunctionStatic()
    {
        $routeCollection = new Collection();
        $routeCollection->get(
            '/homepage/',
            function () {
                return 'executed';
            }
        );

        $matcher  = new Matcher($routeCollection, new DispatcherClosure());
        $executed = $matcher->matchRoute(Method::GET, '/homepage/');

        $this->assertEquals('executed', $executed);
    }

    /**
     * testAnonymousFunctionStaticGetPost
     *
     * @return void
     */
    public function testAnonymousFunctionStaticGetPost()
    {
        $routeCollection = new Collection();
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

        $matcher  = new Matcher($routeCollection, new DispatcherClosure());
        $executed = $matcher->matchRoute(Method::POST, '/homepage/');

        $this->assertEquals('post', $executed);
    }

    /**
     * testAnonymousFunctionStaticAny
     *
     * @return void
     */
    public function testAnonymousFunctionStaticAny()
    {
        $routeCollection = new Collection();
        $routeCollection->any(
            '/homepage/',
            function () {
                return 'any';
            }
        );

        $matcher  = new Matcher($routeCollection, new DispatcherClosure());
        $executed = $matcher->matchRoute(Method::POST, '/homepage/');

        $this->assertEquals('any', $executed);
    }

    /**
     * testRouteNotFoundAnonymousFunction
     *
     * @return void
     */
    public function testRouteNotFoundAnonymousFunction()
    {
        $routeCollection = new Collection();
        $routeCollection->get('/homepage/', function () {
            return 'executed';
        });

        $dispatcher = new DispatcherClosure();
        $dispatcher->setNotFoundHandler(function () {
            return 'not found';
        });

        $matcher  = new Matcher($routeCollection, $dispatcher);
        $executed = $matcher->matchRoute(Method::GET, '/iamnotset/');

        $this->assertEquals('not found', $executed);
    }

    /**
     * testRouteNotFoundControlerHandler
     *
     * @return void
     */
    public function testRouteNotFoundControlerHandler()
    {
        $routeCollection = new Collection();

        $dispatcher = new DispatcherClass($routeCollection);
        $dispatcher->setBaseNamespace('Test\Application');
        $dispatcher->setNotFoundHandler(['controler'=>'videoControler', 'action'=>'notFound']);

        $matcher  = new Matcher($routeCollection, $dispatcher);
        $executed = $matcher->matchRoute(Method::GET, '/iamnotset/');

        $this->assertEquals('not found', $executed);
    }

    /**
     * Test anonymous function with single parameter
     *
     * @return void
     */
    public function testAnonymousFunctionSingleParameter()
    {
        $routeCollection = new Collection();
        $routeCollection->get('/article/{id:\d+}', function ($id) {
            return $id;
        });

        $matcher  = new Matcher($routeCollection, new DispatcherClosure());
        $executed = $matcher->matchRoute(Method::GET, '/article/123');
        $this->assertEquals(123, $executed);
    }

    /**
     * Test anonymous function multiple parameters
     *
     * @return void
     */
    public function testAnonymousFunctionMultipleParameters()
    {
        $routeCollection = new Collection();

        $routeCollection->get('/user/{name}/{id:\d+}', function ($id, $name) {
            $this->assertEquals(123, $id);
            $this->assertEquals('bohuslav', $name);
        });

        $matcher  = new Matcher($routeCollection, new DispatcherClosure());
        $executed = $matcher->matchRoute(Method::GET, '/user/bohuslav/123');
    }

    /**
     * Test anonymous function multiple with some parameters missing
     *
     * @return void
     */
    public function testAnonymousFunctionMultipleParametersSomeMissing()
    {
        $routeCollection = new Collection();

        $routeCollection->get('/user/{name}/{id:\d+}', function ($id, $name) {
            $this->assertEquals(123, $id);
            $this->assertEquals('bohuslav', $name);
        });

        $dispatcherClosure = new DispatcherClosure();
        $dispatcherClosure->setNotFoundHandler(function () {
            return true;
        });

        $matcher  = new Matcher($routeCollection, $dispatcherClosure);
        $executed = $matcher->matchRoute(Method::GET, '/user/bohuslav');
        $this->assertEquals(true, $executed);
    }

    /**
     * Test particular controller and particular action
     *
     * @return void
     */
    public function testParticularControllerParticularAction()
    {
        $routeCollection = new Collection();

        $routeCollection->get(
            '/video/{id:\d+}',
            ['controler'=>'videoControler', 'action'=>'view']
        );

        $dispatcher = new DispatcherClass();
        $dispatcher->setBaseNamespace('Test\Application');

        $matcher = new Matcher($routeCollection, $dispatcher);
        $videoId = $matcher->matchRoute(Method::GET, '/video/123');

        $this->assertEquals(123, $videoId);
    }

    /**
     * Test particular controller dynamic action
     *
     * @return void
     */
    public function testParticularControllerDynamicAction()
    {
        $routeCollection = new Collection();
        $routeCollection->get(
            '/automatics/video/{action}/{id:\d+}',
            ['controler'=>'videoControler', 'action'=>'{action}']
        );

        $dispatcher = new DispatcherClass();
        $dispatcher->setBaseNamespace('Test\Application');

        $matcher = new Matcher($routeCollection, $dispatcher);
        $videoId = $matcher->matchRoute(Method::GET, '/automatics/video/view/123');

        $this->assertEquals(123, $videoId);
    }

    /**
     * Test particular controller dynamic action
     *
     * @return void
     */
    public function testParticularControllerDynamicActionAlternativeOrder()
    {
        $routeCollection = new Collection();
        $routeCollection->get(
            '/automatics/video/{id:\d+}/{action}',
            ['controler'=>'videoControler', 'action'=>'{action}']
        );

        $dispatcher = new DispatcherClass();
        $dispatcher->setBaseNamespace('Test\Application');

        $matcher = new Matcher($routeCollection, $dispatcher);
        $videoId = $matcher->matchRoute(Method::GET, '/automatics/video/123/view');

        $this->assertEquals(123, $videoId);
    }

    /**
     * Test dynamic controller and dynamic action
     *
     * @return void
     */
    public function testDynamicControllerDynamicAction()
    {
        $routeCollection = new Collection();
        $routeCollection->get(
            '/automatics/{controler}/{action}/{id:\d+}',
            ['controler'=>'{controler}', 'action'=>'{action}']
        );

        $dispatcher = new DispatcherClass();
        $dispatcher->setBaseNamespace('Test\Application');

        $matcher = new Matcher($routeCollection, $dispatcher);
        $videoId = $matcher->matchRoute(Method::GET, '/automatics/video/view/123');

        $this->assertEquals(123, $videoId);
    }

    /**
     * Test dynamic controller and dynamic action other order
     *
     * @return void
     */
    public function testDynamicControllerDynamicActionAlternativeOrder()
    {
        $routeCollection = new Collection();
        $routeCollection->get(
            '/automatics/{id:\d+}/{controler}/{action}',
            ['controler'=>'{controler}', 'action'=>'{action}']
        );

        $dispatcher = new DispatcherClass();
        $dispatcher->setBaseNamespace('Test\Application');

        $matcher = new Matcher($routeCollection, $dispatcher);
        $videoId = $matcher->matchRoute(Method::GET, '/automatics/123/video/view');

        $this->assertEquals(123, $videoId);
    }

    /**
     * Test dynamic controller, dynamic action in module
     *
     * @return void
     */
    public function testDynamicControllerDynamicActionStaticModule()
    {
        $routeCollection = new Collection();
        $routeCollection->get(
            '/test/{controler}/{action}/{id:\d+}',
            ['module'=> 'TestModule', 'controler'=>'{controler}', 'action'=>'{action}']
        );

        $dispatcher = new DispatcherClass();
        $dispatcher->setBaseNamespace('Test\Application');

        $matcher = new Matcher($routeCollection, $dispatcher);
        $videoId = $matcher->matchRoute(Method::GET, '/test/test/view/123');

        $this->assertEquals(123, $videoId);
    }

    /**
     * Test dynamic controller, dynamic action in module
     *
     * @return void
     */
    public function testDynamicControllerDynamicActionDynamicModule()
    {
        $routeCollection = new Collection();
        $routeCollection->get(
            '/{module}/{controler}/{action}/{id:\d+}',
            ['module'=> '{module}', 'controler'=>'{controler}', 'action'=>'{action}']
        );

        $dispatcher = new DispatcherClass();
        $dispatcher->setBaseNamespace('Test\Application');

        $matcher = new Matcher($routeCollection, $dispatcher);
        $videoId = $matcher->matchRoute(Method::GET, '/testModule/test/view/123');

        $this->assertEquals(123, $videoId);
    }

    /**
     * Test dynamic controller, dynamic action in module REQUEST
     *
     * @return void
     */
    public function testDynamicControllerDynamicActionDynamicModuleMatch()
    {
        $routeCollection = new Collection();
        $routeCollection->get(
            '/{module}/{controler}/{action}/{id:\d+}',
            ['module'=> '{module}', 'controler'=>'{controler}', 'action'=>'{action}']
        );

        $dispatcher = new DispatcherClass();
        $dispatcher->setBaseNamespace('Test\Application');

        $matcher = new Matcher($routeCollection, $dispatcher);
        $enviromentData = [
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/testModule/test/view/123',
        ];

        $enviroment = new Request\Enviroment($enviromentData);
        $videoId    = $matcher->match(new Request\Request($enviroment));

        $this->assertEquals(123, $videoId);
    }

    /**
     * testRouteNotFoundControlerMissingHandler
     *
     * @return void
     */
    public function testRouteNotFoundControlerMissingHandler()
    {
        $routeCollection = new Collection();
        $routeCollection->get(
            '/{module}/{controler}/{action}/{id:\d+}',
            []
        );

        $dispatcher      = new DispatcherClass($routeCollection);

        $matcher  = new Matcher($routeCollection, $dispatcher);
        $executed = $matcher->matchRoute(Method::GET, '/testModule/test/view/123');

        $this->assertNull($executed);
    }

    /**
     * testRouteNotFoundControlerHandler
     *
     * @return void
     */
    public function testRouteNotFoundControlerHandlerWrongRoute()
    {
        $routeCollection = new Collection();

        $dispatcher = new DispatcherClass($routeCollection);
        $dispatcher->setBaseNamespace('Test\Application');
        $dispatcher->setNotFoundHandler(['controler'=>'videoControler', 'action'=>'notFound']);

        $matcher  = new Matcher($routeCollection, $dispatcher);
        $executed = $matcher->matchRoute(Method::GET, '/iamnotset/');

        $this->assertEquals('not found', $executed);
    }

    /**
     * Test dynamic controller, dynamic action in module REQUEST
     *
     * @return void
     */
    public function testWithoutModeRewrite()
    {
        $routeCollection = new Collection();
        $routeCollection->get(
            '/{module}/{controler}/{action}/{id:\d+}',
            ['module'=> '{module}', 'controler'=>'{controler}', 'action'=>'{action}']
        );

        $dispatcher = new DispatcherClass();
        $dispatcher->setBaseNamespace('Test\Application');

        $matcher = new Matcher($routeCollection, $dispatcher);
        $matcher->setUrlFormat(RouteMode::GET_FORMAT);

        $enviromentData = [
            'QUERY_STRING' => 'r=testModule/test/view/123',
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/',
        ];

        $enviroment = new Request\Enviroment($enviromentData);
        $videoId    = $matcher->match(new Request\Request($enviroment));

        $this->assertEquals(123, $videoId);
    }

    /**
     * Test dynamic controller, dynamic action in module REQUEST
     *
     * @return void
     */
    public function testWithoutModeRewriteFullUrl()
    {
        $routeCollection = new Collection();
        $routeCollection->get(
            '/{module}/{controler}/{action}/{id:\d+}',
            ['module'=> '{module}', 'controler'=>'{controler}', 'action'=>'{action}']
        );

        $dispatcher = new DispatcherClass();
        $dispatcher->setBaseNamespace('Test\Application');

        $matcher = new Matcher($routeCollection, $dispatcher);
        $matcher->setUrlFormat(RouteMode::GET_FORMAT);

        $enviromentData = [
            'QUERY_STRING' => 'r=testModule/test/view/123',
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/index.php?r=testModule/test/view/123',
        ];

        $enviroment = new Request\Enviroment($enviromentData);
        $videoId    = $matcher->match(new Request\Request($enviroment));

        $this->assertEquals(123, $videoId);
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
        $routeCollection = new Collection();
        $dispatcher      = new DispatcherClass();
        $dispatcher->setBaseNamespace('Test\Application');

        $matcher = new Matcher($routeCollection, $dispatcher);
        $matcher->setUrlFormat('invalid value');
    }

    /**
     * Test dynamic controller, dynamic action in module REQUEST
     *
     * @return void
     */
    public function testGetUrlFormat()
    {
        $routeCollection = new Collection();
        $dispatcher      = new DispatcherClass();
        $dispatcher->setBaseNamespace('Test\Application');

        $matcher = new Matcher($routeCollection, $dispatcher);
        $matcher->setUrlFormat(RouteMode::GET_FORMAT);

        $this->assertEquals(RouteMode::GET_FORMAT, $matcher->getUrlFormat());
    }
}

/* partial implementation of PSR-7 request for test purpose */
namespace Kambo\Tests\Router\Request;

class Request
{
    private $serverVariables;
    private $uri;
    private $enviroment;
    private $queryParams;

    public function __construct($enviroment = null)
    {
        $this->enviroment = $enviroment;
        $this->uri = (new Uri($this->enviroment));
    }

    public function createFromServerVariables($serverVariables)
    {
        $this->serverVariables = $serverVariables;
    }

    public function getMethod()
    {
        return $this->enviroment->getRequestMethod();
    }

    public function getQueryParams()
    {
        if ($this->queryParams) {
            return $this->queryParams;
        }

        parse_str($this->uri->getQuery(), $this->queryParams);

        return $this->queryParams;
    }

    public function getUri()
    {
        return $this->uri;
    }
}

class Uri
{
    private $enviroment;

    public function __construct($enviroment = null)
    {
        $this->enviroment = $enviroment;
    }

    public function getPath()
    {
        return $this->enviroment->getRequestUri();
    }

    public function getQuery()
    {
        return $this->enviroment->getQueryString();
    }
}

class Enviroment
{
    private $enviromentData = null;

    public function __construct($enviromentData = null)
    {
        $this->fromArray($enviromentData);
    }

    public function fromArray($enviromentData)
    {
        $this->enviromentData = $enviromentData;
    }

    public function getQueryString()
    {
        return $this->enviromentData['QUERY_STRING'];
    }

    public function getRequestMethod()
    {
        return $this->enviromentData['REQUEST_METHOD'];
    }

    public function getRequestUri()
    {
        return $this->enviromentData['REQUEST_URI'];
    }
}

/* test application */
namespace Test\Application\Controllers;

class videoControler
{
    public function actionView($id)
    {
        return $id;
    }

    public function actionNotFound()
    {
        return 'not found';
    }
}

namespace Test\Application\Modules\TestModule\Controllers;

class testControler
{
    public function actionview($id)
    {
        return $id;
    }
}
