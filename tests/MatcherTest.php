<?php
namespace Test;

use Kambo\Router\Route\RouteCollection;
use Kambo\Router\Dispatchers\Dispatcher;
use Kambo\Router\Dispatchers\DispatcherClosure;
use Kambo\Router\Dispatchers\DispatcherClass;
use Kambo\Router\Matcher;

use Kambo\Router\Enum\Method;
use Kambo\Router\Enum\RouteMode;

use Application\Controllers;

class MatcherTest extends \PHPUnit_Framework_TestCase {
    
    /**
     * testAnonymousFunctionStatic
     * 
     * @return void
     */ 
    public function testAnonymousFunctionStatic() {
        $routeCollection = new RouteCollection();
        $routeCollection->get('/homepage/', function() {
            return 'executed';     
        });

        $matcher  = new Matcher($routeCollection, new DispatcherClosure());
        $executed = $matcher->matchRoute(Method::GET, '/homepage/');

        $this->assertEquals('executed', $executed);
    }

    /**
     * testAnonymousFunctionStaticGetPost
     * 
     * @return void
     */ 
    public function testAnonymousFunctionStaticGetPost() {
        $routeCollection = new RouteCollection();
        $routeCollection->get('/homepage/', function() {
            return 'get';     
        });        
        $routeCollection->post('/homepage/', function() {
            return 'post';     
        });

        $matcher  = new Matcher($routeCollection, new DispatcherClosure());
        $executed = $matcher->matchRoute(Method::POST, '/homepage/');

        $this->assertEquals('post', $executed);
    }

    /**
     * testAnonymousFunctionStaticAny
     * 
     * @return void
     */ 
    public function testAnonymousFunctionStaticAny() {
        $routeCollection = new RouteCollection();
        $routeCollection->any('/homepage/', function() {
            return 'any';     
        });

        $matcher  = new Matcher($routeCollection, new DispatcherClosure());
        $executed = $matcher->matchRoute(Method::POST, '/homepage/');

        $this->assertEquals('any', $executed);
    }

    /**
     * testRouteNotFoundAnonymousFunction
     * 
     * @return void
     */ 
    public function testRouteNotFoundAnonymousFunction() {
        $routeCollection = new RouteCollection();
        $routeCollection->get('/homepage/', function() {
            return 'executed';     
        });

        $dispatcher = new DispatcherClosure();
        $dispatcher->setNotFoundHandler(function() {
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
    public function testRouteNotFoundControlerHandler() {
        $routeCollection = new RouteCollection();
        $routeCollection->get('/homepage/', function() {
            return 'executed';     
        });

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
    public function testAnonymousFunctionSingleParameter() {
        $routeCollection = new RouteCollection();
        $routeCollection->get('/article/{id:\d+}', function($id) {
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
    public function testAnonymousFunctionMultipleParameters() {
        $routeCollection = new RouteCollection();

        $routeCollection->get('/user/{name}/{id:\d+}', function($id, $name) {
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
    public function testAnonymousFunctionMultipleParametersSomeMissing() {
        $routeCollection = new RouteCollection();

        $routeCollection->get('/user/{name}/{id:\d+}', function($id, $name) {
            $this->assertEquals(123, $id);
            $this->assertEquals('bohuslav', $name);
        });

        $dispatcherClosure = new DispatcherClosure();
        $dispatcherClosure->setNotFoundHandler(function() {
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
    public function testParticularControllerParticularAction() {
        $routeCollection = new RouteCollection();

        // separated object for route? static route, dynamic route, module route...
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
    public function testParticularControllerDynamicAction() {
        $routeCollection = new RouteCollection();
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
    public function testParticularControllerDynamicActionAlternativeOrder() {
        $routeCollection = new RouteCollection();
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
    public function testDynamicControllerDynamicAction() {
        $routeCollection = new RouteCollection();
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
    public function testDynamicControllerDynamicActionAlternativeOrder() {
        $routeCollection = new RouteCollection();
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
    public function testDynamicControllerDynamicActionStaticModule() {
        $routeCollection = new RouteCollection();
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
    public function testDynamicControllerDynamicActionDynamicModule() {
        $routeCollection = new RouteCollection();
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
    public function testDynamicControllerDynamicActionDynamicModuleMatch() {
        $routeCollection = new RouteCollection();
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
     * Test dynamic controller, dynamic action in module REQUEST 
     * 
     * @return void
     */  
    public function testWithoutModeRewrite() {
        $routeCollection = new RouteCollection();
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
    public function testWithoutModeRewriteFullUrl() {
        $routeCollection = new RouteCollection();
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

}

/* partial implementation of PSR-7 request for test purpose */
namespace Test\Request;

class Request
{
    private $_serverVariables;
    private $_uri;
    private $_enviroment;
    private $_queryParams;

    function __construct($enviroment = null) {
        $this->_enviroment = $enviroment;
        $this->_uri = (new Uri($this->_enviroment));
    }

    public function createFromServerVariables($serverVariables) {
        $this->_serverVariables = $serverVariables;
    }

    public function getMethod() {
        return $this->_enviroment->getRequestMethod();
    }

    public function getQueryParams() {
        if ($this->_queryParams) {
            return $this->_queryParams;
        }

        parse_str($this->_uri->getQuery(), $this->_queryParams);

        return $this->_queryParams;
    }

    public function getUri() {
        return $this->_uri;  
    }    
}

class Uri
{
    private $_enviroment;

    function __construct($enviroment = null) {
        $this->_enviroment = $enviroment;
    }

    public function getPath() {
        return $this->_enviroment->getRequestUri();    
    }

    public function getQuery() {
        return $this->_enviroment->getQueryString();    
    }
}

class Enviroment
{
    private $_enviromentData = null;

    function __construct($enviromentData = null) {
        $this->fromArray($enviromentData);
    }    

    public function fromArray($enviromentData) {
        $this->_enviromentData = $enviromentData;    
    }

    public function getQueryString() {
        return $this->_enviromentData['QUERY_STRING'];
    }

    public function getRequestMethod() {
        return $this->_enviromentData['REQUEST_METHOD'];
    }

    public function getRequestUri() {
        return $this->_enviromentData['REQUEST_URI'];
    }      
}

/* test application */
namespace Test\Application\Controllers;

class videoControler
{
    public function actionView($id) {
        return $id;
    }

    public function actionNotFound() {
        return 'not found';
    }

}

namespace Test\Application\Modules\TestModule\Controllers;

class testControler
{
    public function actionview($id) {
        return $id;
    }
}