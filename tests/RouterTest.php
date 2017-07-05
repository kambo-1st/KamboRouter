<?php
namespace Kambo\Tests\Router;

use PHPUnit\Framework\TestCase;

require_once __DIR__.'/Request/Enviroment.php';
require_once __DIR__.'/Request/Request.php';
require_once __DIR__.'/Request/Uri.php';
require_once __DIR__.'/Application/Modules/TestModule/Controllers/TestControler.php';
require_once __DIR__.'/Application/Controllers/VideoControler.php';

use Kambo\Router\Route\Collection;
use Kambo\Router\Route\Builder\Base;

use Kambo\Router\Dispatcher\ClosureAutoBind;
use Kambo\Router\Dispatcher\ClassAutoBind;

use Kambo\Router\Router;

use Kambo\Router\Matcher\Regex;

use Kambo\Router\Enum\Method;
use Kambo\Router\Enum\RouteMode;

use Kambo\Tests\Router\Request\Enviroment;
use Kambo\Tests\Router\Request\Request;

/**
 * Tests for Router class
 *
 * @package Kambo\Tests\Router
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license MIT
 */
class RouterTest extends TestCase
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

        $dispatcher = new ClosureAutoBind();
        $matcher    = new Regex($routeCollection);

        $request = $this->getRequest(Method::GET, '/homepage/');
        $router  = $this->getRouter($dispatcher, $matcher);
        $result  = $router->dispatch($request, []);

        $this->assertEquals('executed', $result);
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

        $dispatcher = new ClosureAutoBind();
        $matcher    = new Regex($routeCollection);

        $request = $this->getRequest(Method::POST, '/homepage/');
        $router  = $this->getRouter($dispatcher, $matcher);
        $result  = $router->dispatch($request, []);


        $this->assertEquals('post', $result);
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

        $dispatcher = new ClosureAutoBind();
        $matcher    = new Regex($routeCollection);

        $request = $this->getRequest(Method::POST, '/homepage/');
        $router  = $this->getRouter($dispatcher, $matcher);
        $result  = $router->dispatch($request, []);

        $this->assertEquals('any', $result);
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

        $dispatcher = new ClosureAutoBind();
        $dispatcher->setNotFoundHandler(function () {
            return 'not found';
        });

        $matcher = new Regex($routeCollection);

        $request = $this->getRequest(Method::GET, '/iamnotset/');
        $router  = $this->getRouter($dispatcher, $matcher);
        $result  = $router->dispatch($request, []);

        $this->assertEquals('not found', $result);
    }

    /**
     * Tests execution of class for not found handler.
     *
     * @return void
     */
    public function testRouteNotFoundControlerHandler()
    {
        $routeCollection = new Collection(new Base());

        $dispatcher = new ClassAutoBind();
        $dispatcher->setBaseNamespace('Kambo\Tests\Router\Application');
        $dispatcher->setNotFoundHandler(['controler'=>'videoControler', 'action'=>'notFound']);

        $matcher = new Regex($routeCollection);

        $request = $this->getRequest(Method::GET, '/iamnotset/');
        $router  = $this->getRouter($dispatcher, $matcher);
        $result  = $router->dispatch($request, []);

        $this->assertEquals('not found', $result);
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

        $dispatcher = new ClosureAutoBind();
        $matcher    = new Regex($routeCollection);

        $request = $this->getRequest(Method::GET, '/article/123');
        $router  = $this->getRouter($dispatcher, $matcher);
        $result  = $router->dispatch($request, []);

        $this->assertEquals(123, $result);
    }

    /**
     * Test anonymous function multiple parameters
     *
     * @return void
     */
    public function testAnonymousFunctionMultipleParameters()
    {
        $routeCollection = new Collection(new Base());

        $routeCollection->get('/user/{name}/{id:\d+}', function ($id, $name) {
            $this->assertEquals(123, $id);
            $this->assertEquals('bohuslav', $name);
        });

        $dispatcher = new ClosureAutoBind();
        $matcher    = new Regex($routeCollection);

        $request = $this->getRequest(Method::GET, '/user/bohuslav/123');
        $router  = $this->getRouter($dispatcher, $matcher);
        $router->dispatch($request, []);
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

        $request = $this->getRequest(Method::GET, '/user/bohuslav');
        $router  = $this->getRouter($dispatcherClosure, $matcher);
        $result  = $router->dispatch($request, []);

        $this->assertEquals(true, $result);
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
            ['controler'=>'videoControler', 'action'=>'view']
        );

        $dispatcher = new ClassAutoBind();
        $dispatcher->setBaseNamespace('Kambo\Tests\Router\Application');

        $matcher = new Regex($routeCollection);

        $request = $this->getRequest(Method::GET, '/video/123');
        $router  = $this->getRouter($dispatcher, $matcher);
        $result  = $router->dispatch($request, []);

        $this->assertEquals(123, $result);
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
            ['controler'=>'videoControler', 'action'=>'{action}']
        );

        $dispatcher = new ClassAutoBind();
        $dispatcher->setBaseNamespace('Kambo\Tests\Router\Application');

        $matcher = new Regex($routeCollection);

        $request = $this->getRequest(Method::GET, '/automatics/video/view/123');
        $router  = $this->getRouter($dispatcher, $matcher);
        $result  = $router->dispatch($request, []);

        $this->assertEquals(123, $result);
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
            ['controler'=>'videoControler', 'action'=>'{action}']
        );

        $dispatcher = new ClassAutoBind();
        $dispatcher->setBaseNamespace('Kambo\Tests\Router\Application');

        $matcher = new Regex($routeCollection);

        $request = $this->getRequest(Method::GET, '/automatics/video/123/view');
        $router  = $this->getRouter($dispatcher, $matcher);
        $result  = $router->dispatch($request, []);

        $this->assertEquals(123, $result);
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
            ['controler'=>'{controler}', 'action'=>'{action}']
        );

        $dispatcher = new ClassAutoBind();
        $dispatcher->setBaseNamespace('Kambo\Tests\Router\Application');

        $matcher = new Regex($routeCollection);

        $request = $this->getRequest(Method::GET, '/automatics/video/view/123');
        $router  = $this->getRouter($dispatcher, $matcher);
        $result  = $router->dispatch($request, []);

        $this->assertEquals(123, $result);
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
            ['controler'=>'{controler}', 'action'=>'{action}']
        );

        $dispatcher = new ClassAutoBind();
        $dispatcher->setBaseNamespace('Kambo\Tests\Router\Application');

        $matcher = new Regex($routeCollection);

        $request = $this->getRequest(Method::GET, '/automatics/123/video/view');
        $router  = $this->getRouter($dispatcher, $matcher);
        $result  = $router->dispatch($request, []);

        $this->assertEquals(123, $result);
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
        $dispatcher->setBaseNamespace('Kambo\Tests\Router\Application');

        $matcher = new Regex($routeCollection);

        $request = $this->getRequest(Method::GET, '/test/test/view/123');
        $router  = $this->getRouter($dispatcher, $matcher);
        $result  = $router->dispatch($request, []);

        $this->assertEquals(123, $result);
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
                'controler' => '{controler}',
                'action' => '{action}'
            ]
        );

        $dispatcher = new ClassAutoBind();
        $dispatcher->setBaseNamespace('Kambo\Tests\Router\Application');

        $matcher = new Regex($routeCollection);

        $request = $this->getRequest(Method::GET, '/testModule/test/view/123');
        $router  = $this->getRouter($dispatcher, $matcher);
        $result  = $router->dispatch($request, []);

        $this->assertEquals(123, $result);
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
        $dispatcher->setBaseNamespace('Kambo\Tests\Router\Application');

        $matcher = new Regex($routeCollection);

        $request = $this->getRequest(Method::GET, '/testModule/test/view/123');
        $router  = $this->getRouter($dispatcher, $matcher);
        $result  = $router->dispatch($request, []);

        $this->assertEquals(123, $result);
    }

    /**
     * testRouteNotFoundControlerMissingHandler
     *
     * @return void
     */
    public function testRouteNotFoundControlerMissingHandler()
    {
        $routeCollection = new Collection(new Base());
        $routeCollection->get(
            '/{module}/{controler}/{action}/{id:\d+}',
            []
        );

        $dispatcher = new ClassAutoBind($routeCollection);

        $matcher  = new Regex($routeCollection);

        $request = $this->getRequest(Method::GET, '/testModule/test/view/123');
        $router  = $this->getRouter($dispatcher, $matcher);
        $result  = $router->dispatch($request, []);

        $this->assertNull($result);
    }

    /**
     * testRouteNotFoundControlerHandler
     *
     * @return void
     */
    public function testRouteNotFoundControlerHandlerWrongRoute()
    {
        $routeCollection = new Collection(new Base());

        $dispatcher = new ClassAutoBind($routeCollection);
        $dispatcher->setBaseNamespace('Kambo\Tests\Router\Application');
        $dispatcher->setNotFoundHandler(
            [
                'controler' => 'videoControler',
                'action' => 'notFound'
            ]
        );

        $matcher  = new Regex($routeCollection);

        $request = $this->getRequest(Method::GET, '/testModule/test/view/123');
        $router  = $this->getRouter($dispatcher, $matcher);
        $result  = $router->dispatch($request, []);

        $this->assertEquals('not found', $result);
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
            [
                'module' => '{module}',
                'controler' =>'{controler}',
                'action' =>'{action}'
            ]
        );

        $dispatcher = new ClassAutoBind();
        $dispatcher->setBaseNamespace('Kambo\Tests\Router\Application');

        $matcher = new Regex($routeCollection);
        $matcher->setUrlFormat(RouteMode::GET_FORMAT);

        $request = $this->getRequest(Method::GET, '/', 'r=testModule/test/view/123');
        $router  = $this->getRouter($dispatcher, $matcher);
        $result  = $router->dispatch($request, []);

        $this->assertEquals(123, $result);
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
            [
                'module' => '{module}',
                'controler' => '{controler}',
                'action' => '{action}'
            ]
        );

        $dispatcher = new ClassAutoBind();
        $dispatcher->setBaseNamespace('Kambo\Tests\Router\Application');

        $matcher = new Regex($routeCollection);
        $matcher->setUrlFormat(RouteMode::GET_FORMAT);

        $request = $this->getRequest(
            Method::GET,
            '/index.php?r=testModule/test/view/123',
            'r=testModule/test/view/123'
        );

        $router = $this->getRouter($dispatcher, $matcher);
        $result = $router->dispatch($request, []);

        $this->assertEquals(123, $result);
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

        $matcher = new Regex($routeCollection);
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

        $matcher = new Regex($routeCollection);
        $matcher->setUrlFormat(RouteMode::GET_FORMAT);

        $this->assertEquals(RouteMode::GET_FORMAT, $matcher->getUrlFormat());
    }

    // ------------ PRIVATE METHODS

    /**
     * Get instance of the Router object for testing
     *
     * @return Router
     */
    private function getRouter($dispatcher, $matcher) : Router
    {
        $router = new Router($dispatcher, $matcher);

        return $router;
    }

    /**
     * Get instance of the Request object for testing
     *
     * @return Request
     */
    private function getRequest(string $method, string $url = '/', string $query = '') : Request
    {
        $enviromentData = [
            'QUERY_STRING' => $query,
            'REQUEST_METHOD' => $method,
            'REQUEST_URI' => $url,
        ];

        $enviroment = new Enviroment($enviromentData);

        return new Request($enviroment);
    }
}
