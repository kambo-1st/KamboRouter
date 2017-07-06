<?php

require 'vendor/autoload.php';

// Kambo\Router
use Kambo\Router\Route\Collection;
use Kambo\Router\Route\Builder\Base;
use Kambo\Router\Dispatcher\ClosureAutoBind;
use Kambo\Router\Router;
use Kambo\Router\Matcher\Regex;

// Kambo\Http\Message
use Kambo\Http\Message\Environment\Environment;
use Kambo\Http\Message\Factories\Environment\ServerRequestFactory;

$routeCollection = new Collection(new Base());

// Matches http://{domain}/user/{string}/transaction/{integer number} eg.: http://router-example.vg/user/kambo/transaction/1
$routeCollection->get('/user/{name}/transaction/{id:\d+}', function(int $id, string $name) {
    echo $id.' '.$name;
});

// Matches http://{domain}/article/{integer number} eg.: http://router-example.vg/article/42
$routeCollection->get('/article/{id:\d+}', function(int $id) {
    echo 'article id: '.$id;
});

// Create instance of the closure dispatcher with function properties auto bind functionality
$dispatcherClosureAutoBind = new ClosureAutoBind();

// Create instance of the route matcher based on regular expressions
$matcherRegex = new Regex($routeCollection);

// Create instance of the Router
$router = new Router($dispatcherClosureAutoBind, $matcherRegex);

// Create Environment object based on server variables.
$environment = new Environment($_SERVER, fopen('php://input', 'w+'), $_POST, $_COOKIE, $_FILES);

// Create instance of ServerRequest object in this example we are using Kambo/HttpMessage (https://github.com/kambo-1st/HttpMessage)
// but any other implementation of PSR 7 server request can be used.
$request = (new ServerRequestFactory())->create($environment);

// Start URL matching a PSR 7 compatible object must be provided
$router->dispatch($request);
