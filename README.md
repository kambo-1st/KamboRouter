# Kambo PHP router
[![Build Status](https://travis-ci.org/kambo-1st/KamboRouter.svg?branch=master)](https://travis-ci.org/kambo-1st/KamboRouter)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/kambo-1st/KamboRouter/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/kambo-1st/KamboRouter/?branch=master)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/kambo-1st/KamboRouter.svg?style=flat-square)](https://scrutinizer-ci.com/g/kambo-1st/KamboRouter/)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

Just another PHP router with following highlights:

* Support of PSR-7 - HTTP message interfaces
* Two dispatchers with closure and controller/module support 
* Can be used even without mod_rewrite

## Install

Prefered way to install library is with composer:
```sh
composer require kambo/router
```

## Usage

### URL rewrite

For rewrite support in Apache with enabled mod_rewrite create a .htaccess file in your root directory, with following settings:

```apache
Options +FollowSymLinks
RewriteEngine On
RewriteRule ^(.*)$ index.php [NC,L]
```

For disabling support of mod_rewrite use method setUrlFormat:
```php
$matcher->setUrlFormat(RouteMode::GET_FORMAT);
```

### Route definition
The routes are added by calling `createRoute()` on the RouteCollection instance:

```php
$routeCollection->createRoute($method, $routePattern, $handler);
```

The `$method` is HTTP method name represented by value from Kambo\Router\Enum\Method enum for which a certain route should match, eg.: Method::GET

By default the `$routePattern` uses a syntax where `{foo}` specifies a placeholder with name `foo`
and matching the regex `[^/]+`. To adjust the pattern the placeholder matches, you can specify
a custom pattern by writing `{bar:[0-9]+}`. Some examples:

```php
// Matches /user/kambo/123, but not /user/kambo/abc
$routeCollection->addRoute(Method::GET, '/user/{name}/{id:\d+}', $handler);
```

A shortcut methods can be also used for all Method:

```php
// Shortcut for createRoute(Method::GET, '/user/{name}/{id:\d+}', $handler);

$routeCollection->get('/user/{name}/{id:\d+}', $handler) 
$routeCollection->post('post/url', $handler)
$routeCollection->delete('delete/url', $handler)
$routeCollection->put('put/url', $handler)
$routeCollection->any('any/url', $handler)
```
A closure as `$handler` can be used:

```php
$routeCollection->get('/article/{id:\d+}', function($id) {
    echo $id;
});
```

### PSR-7 - HTTP message interfaces
Kambo router is using a instance of PSR 7 compatible request object for abstraction over server variables. Any third party library that implements PSR-7 can be used,
such as [Kambo/HttpMessage](https://github.com/kambo-1st/HttpMessage)

### Router dispatcher

Router comes with following dispatcher:

* Closure dispatcher with automatic path <=> closure variable bind function.
* Opinionated class dispatcher which force you organize your code into module/controller class structure.

#### Using closure dispatcher

```php
<?php

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

```

This example will define two routes:

http://{domain}/user/{string}/transaction/{integer number}
http://{domain}/article/{integer number}


## License
The MIT License (MIT), https://opensource.org/licenses/MIT
