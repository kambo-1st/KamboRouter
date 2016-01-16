# Kambo PHP router
[![Build Status](https://img.shields.io/travis/kambo-1st/KamboRouter.svg?branch=master&style=flat-square)](https://travis-ci.org/kambo-1st/KamboRouter)

Just another PHP router with following highlights:

* Support of PSR-7 - HTTP message interfaces
* Two dispatchers with closure and controller/module supopport 
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
The routes are added by calling `addRoute()` on the RouteCollection instance:

```php
$routeCollection->addRoute($method, $routePattern, $handler);
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
// Shortcut for addRoute(Method::GET, '/user/{name}/{id:\d+}', $handler);
$routeCollection->get('/user/{name}/{id:\d+}', $handler) 
$routeCollection->post($route, $handler)
$routeCollection->delete($route, $handler)
$routeCollection->put($route, $handler)
$routeCollection->any($route, $handler)
```
A closure as $handler can be used:

```php
$routeCollection->get('/article/{id:\d+}', function($id) {
    echo $id;
});
```

### PSR-7 - HTTP message interfaces
Kambo router is using a instance of PSR 7 compatible request object for abstraction over server variables. Any third party library that implements PSR-7 can be used, also a minimal viable implementation is provided in examples/Request.php 

### Using closure dispatcher

```php
<?php

use Kambo\Router\Route\RouteCollection;
use Kambo\Router\Dispatchers\DispatcherClosure;
use Kambo\Router\Matcher;

require 'Router/vendor/autoload.php';

$routeCollection = new RouteCollection();

$routeCollection->get('/user/{name}/{id:\d+}', function($id, $name) {
    echo $id.' '.$name;
});

$routeCollection->get('/article/{id:\d+}', function($id) {
    echo $id;
});

$dispatcherClosure = new DispatcherClosure();
$matcher           = new Matcher($routeCollection, $dispatcherClosure);

// Start URL matching a PSR 7 compatible object must be provided
$matcher->match(/* instance of PSR 7 compatible request object */);
```

This example will define two routes:

http://{domain}/user/{any name}/{integer number}
http://{domain}/article/{integer number}


### Using dispatcher Controller

```php
<?php

use Kambo\Router\Route\RouteCollection;
use Kambo\Router\Dispatchers\DispatcherClosure;
use Kambo\Router\Dispatchers\DispatcherController;
use Kambo\Router\Matcher;


$loader = require 'Router/vendor/autoload.php';
$loader->setPsr4('Application\\Controllers\\', 'Application/Controllers');

$routeCollection = new RouteCollection();

$routeCollection->get(
    '/video/{id:\d+}',
    ['controler'=>'videoControler', 'action'=>'view']
);

$routeCollection->get(
    '/advert/{controler}/{action}/{id:\d+}',
    ['controler'=>'{controler}', 'action'=>'{action}']
);


$dispatcherController = new DispatcherController();
// Set basenamespace for controller resolving.
$dispatcherController->setBaseNamespace('Application');

$matcher = new Matcher($routeCollection, $dispatcherController);

// Start URL matching a PSR 7 compatible object must be provided.
$matcher->match(/* instance of PSR 7 compatible object */);

```

This example will define two routes:

http://{domain}/video/{integer number}
http://{domain}/advert/{controler}/{action}/{integer number}

## License
Apache License, Version 2.0, http://opensource.org/licenses/Apache-2.0