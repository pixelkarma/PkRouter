# PkRouter

PkRouter is a fast and robust modern PHP router intended to be used for API creation. It has no dependencies, and encourages an object oriented approach.

## Features

- Supports all HTTP methods, including custom ones.
- Flexible middleware for pre- and post-route execution.
- Extensible request and response classes for handling custom payloads.
- Supports both function and class method callbacks, ideal for MVC projects.
- Efficient, robust regex-based routing.
- Extensible parameter pattern validation.
- Dynamic properties enable seamless data storage throughout the request lifecycle.
- Custom exceptions enhance error handling and clarity.

## Installation

Composer package soon.

## Usage

```php
use Pixelkarma\PkRouter\PkRouter;

$pkRouter = new PkRouter();
```

### Functions

```php
use Pixelkarma\PkRouter\PkRoute;

$pkRouter->routes->addRoute(
  new PkRoute(
    "home", ["GET"], "/",
    function (PkRouter $router) {
      $message = "Hello World!";
      return $router->respond(["message" => $message]);
    }
  )
);
$pkRouter->run();
```

### Controller Methods

See [Controller Method Callbacks](#controller-method-callbacks)

```php
# ./public/index.php
$pkRouter->routes->addRoute(
  new PkRoute("home", ["GET"], "/", "YourNamespace\Controllers\HelloController@sayHello")
);
$pkRouter->run();
```

## Router (`PkRouter`)

```php
  $pkRouter = new PkRouter(
    $routes = null, // An instance of PkRoutesConfig
    $request = null, // An instance of PkRequest
    $response = null, // An instance of PkResponse
    $logFunction = null // a callable function that excepts one Exception parameter
  );
```

If no options are passed when creating the router, the default classes are
used and an empty [Route Config](#route-config) is created. You will then
need to add routes individually.

```php
use Pixelkarma\PkRouter\PkRoute;

$homeRoute = new PkRoute(
  name: "home",
  path: "/",
  methods: ["GET"],
  callback: /* function or class method string */,
  meta: [],
  before: [
    /* PkMiddlewareInterface */
  ],
  after: [
    /* PkMiddlewareInterface */
  ],
);

$pkRouter->routes->addRoute($homeRoute);
```

### Finding a match

There are two ways to find a match and execute its callback.

```php
$boolResult = $pkRouter->run();
```

or

```php
if (true === $pkRouter->match()) {
  $boolResult = $pkRouter->run();
}else{
  // Handle the 404
}
```

### Responding

> This is a _shortcut_ to `$router->response->sendJson();`

```php
$router->respond(["key"=>"value"], 200);
```

### Dynamic Properties

In PkRouter, you have the ability to set dynamic properties on the $router instance.
These properties can be used to store data that needs to be accessed or modified at
different stages of the request lifecycle, such as during the before middleware, the
route callback, and the after middleware.

```php
// Setting up a user object
$router->user = (object)[
  "username" => "j.doe"
];
```

# Route Creation (`PkRoute`)

```php
new PkRoute(
  name: "name",
  path: "/path/",
  methods: ["GET", "POST"],
  callback: "YourNamespace\Controllers\HelloController@sayHello",
  meta: [
    "permissions" => "admin"
    "anything" => "else"
  ],
  before: [
    // PkMiddlewareInterface, - run before route
    // PkMiddlewareInterface, ...
  ],
  after: [
    // PkMiddlewareInterface, - run after route
    // PkMiddlewareInterface, ...
  ]
);
```

## Route Callback

The callback is executed when a matching route is found. This value can either
be a function, or a string representing your own controller method. Both should
accept one parameter, an instance of `PkRouter`

### Functional Callbacks

```php
// Function
callback: functionName(PkRouter $router)

// Anonymous Function
callback: function(PkRouter $router){
  $router->respond(["message" => "Hello!"]);
}
```

### Controller Method Callbacks

```php
// Controller Method
callback: "YourNamespace\Controllers\ExampleController@methodName"
```

Your Contoller will need to accept the `$router` on construct, not the method.

```php
namespace YourNamespace\Controllers;
class ExampleController {
  private $router;
  public function __construct(PkRouter $router) {
    $this->router = $router;
  }
  public function methodName(){
    return $this->router->respond(["message" => "Hello!"]);
  }
}
```

## Route Meta

Meta is an array of values used to provide additional data on a route level. This information
is accessible from [Middleware](#middleware) and Callbacks

```php
meta: [
  "userAccess" => ["group1", "group2"],
  "something" => "else"
]
```

## Before and After Middleware

See [Middleware](#middleware)

## URL Parameters

> Path: `/user/[s:username]/`

In this example `s`, is the type and `username` is the param name accessible with:

```php
$router->request->getParam('username')
```

See [PkRoute Methods](#pkroute-methods) for more information about `getParam()`

|  Type  | Description                        |
| :----: | ---------------------------------- |
|  `i`   | Integer                            |
|  `f`   | Float                              |
|  `a`   | Alpha                              |
|  `n`   | Alphanumeric                       |
|  `s`   | String (URL acceptable characters) |
|  `e`   | Email                              |
|  `b`   | Binary: 1, 0, true, false          |
|  `ip`  | IPV4 Address                       |
| `ipv6` | IPV6 Address                       |
|  `uu`  | UUID String                        |
| `uu4`  | UUID Version 4 String              |
| `ymd`  | Date: YYYY-MM-DD                   |
| `hms`  | Time: H:i:s                        |
|  `*`   | Wildcard — Be careful.             |

### Add additional match patterns

By calling the static function _addMatchPattern_, you can add additional
param types. This can be used at any point prior to matching a route.

A common place to put this is in a Route Config file, as shown below

```php
use Pixelkarma\PkRouter\PkRoute;
use Pixelkarma\PkRouter\PkRoutesConfig;

class MyRoutes extends PkRoutesConfig {
  public function routes(): array {
    /**
     *  Match 'Serial Number' 'AA-1234'
     *  /path/AA-1234/ == /path/[sn:serialNumber]/
     */
    PkRoute::addMatchPattern("sn", "(/^[A-Z]{2}-\d{4}$/)");
    /* add routes here */
  }
}
```

## Route Methods

Methods that allow the `$key` parameter will return `$default` (default: null) if the key is not found.
This is useful when validating a payload when you might want a value other than `null`.

```php
$router->route->getParam("userId", false);
$router->route->getParam("userId", 0);
```

```php
// Returns key value or all `meta` with `getMeta('name')`
$router->route->getMeta(string $key = null, $default = null)

// Returns key value or all `params` with `getParam('name')`
$router->route->getParam(string $key = null, $default = null)

// Returns route name string
$router->route->getName()

// Returns the path `/user/[s:username]/`
$router->route->getPath()

// Returns an array of allowed methods `["GET", "POST"]`
$router->route->getMethods()
```

# Route Config (`PkRoutesConfig`)

`PkRoutesConfig` is an extensible class for setting up multiple Routes, and adding Middleware.

```php
# ./public/index.php
use Pixelkarma\PkRouter\PkRouter;
$pkRouter = new PkRouter(new MyRoutes());
$pkRouter->run();
```

```php
# ./src/Routes/MyRoutes.php
namespace YourNamespace\Router;

use YourNamespace\Router\Middleware\AuthorizationMiddleware;
use YourNamespace\Router\Middleware\AnalyticsMiddleware;

use Pixelkarma\PkRouter\PkRoute;
use Pixelkarma\PkRouter\PkRoutesConfig;

class MyRoutes extends PkRoutesConfig {
  public function routes(): array {

    // Define Middleware
    $authorizationMiddleware = new AuthorizationMiddleware();
    $analyticsMiddleware = new AnalyticsMiddleware();

    // Create Routes
    $readRoute = new PkRoute(
      name: "read",
      path: "/storage/",
      methods: ["GET"],
      callback: "YourNamespace\Controllers\StorageController@read",
      after: [
        $analyticsMiddleware,
      ]
    );

    $writeRoute = new PkRoute(
      name: "write",
      path: "/storage/",
      methods: ["POST"],
      callback: "YourNamespace\Controllers\StorageController@write",
      before: [
        $authorizationMiddleware,
      ],
      after: [
        $analyticsMiddleware,
      ]
    );

    // Return all routes
    return [$readRoute, $writeRoute];
  }
}
```

# Request (`PkRequest`)

`PkRequest` contains all of the information about the request being made.

### Request Methods

Methods that allow the `$key` parameter will return `$default` (default: null) if there is no data.
This is useful when validating a payload when you might want a value other than `null`.

```php
$router->request->getHeader("authorization", false);
$router->request->getHeader("user-agent", "Unknown");
```

```php
// Returns key value or all `headers` with `getHeader('name')`
$router->request->getHeader(string $key = null, $default = null);

// Returns key value or all `?query=string` with `getQuery('name')`
$router->request->getQuery(string $key = null, $default = null);

// Returns key value or all `body content` with `getBody('name')`
$router->request->getBody(string $key = null, $default = null);

// Returns key value or all `cookies` with `getCookie('name')`
$router->request->getCookie(string $key = null, $default = null);

// Returns key value or all `$_FILES` with `getFile('name')`
$router->request->getFile(string $key = null, $default = null);

// Returns a string like "GET" or "POST"
$router->request->getMethod();

// Returns true if the connection is SSL
$router->request->isSecure();

// Returns the hostname
$router->request->getHost();

// Returns the requested path
$router->request->getPath();

// Returns the Content-Type, usually `application/json`
$router->request->getContentType();

// Returns the RAW body of the request.
$router->request->getRawBody();

// Returns "https" or "http"
$router->request->getScheme();

// Returns the port: 80, 443
$router->request->getPort();

// Returns the `username` in http://username:password@hostname/path
// !DANGER! This is insecure and depreciated. Use with caution.
$router->request->getUser();

// Returns the `password` in http://username:password@hostname/path
// !DANGER! This is insecure and depreciated. Use with caution.
$router->request->getPass();
```

# Response (`PkResponse`)

Headers and response code do not send until the _payload_ sends.

```php
// Add a response header.
$router->response->setHeader(string $name, string $value = '');

// Clear all set response headers
$router->response->clearHeaders();

// Set the response code (200, 404, 500, ...)
$router->response->setCode(int $code);

// Sends an Array as JSON.
// Optionally set the http response code.
$router->response->sendJson(array $payload, int $code = null);
/* also */ $router->respond(array $payload, int $code = null);

// Sends whatever you pass without processing it.
// Optionally set the http response code.
$router->response->sendRaw($payload, int $code = null);

// Returns the response payload exactly how it was sent.
$router->response->getPayload();
```

## Extending `PkResponse`

PkRouter only supports JSON out of the box, but you can extend it to do more.

```php
# ./src/Router/CustomResponse.php
namespace YourNamespace\Router;

use Pixelkarma\PkRouter\PkResponse;

class CustomResponse extends PkResponse {
  public function sendXml($xml, int $code = null): bool {
    $this->setHeader('Content-Type', 'application/xml');
    return $this->sendRaw($xml, $code);
  }
}
```

```php
# ./public/index.php
use YourNamespace\Router\CustomResponse;

// Create PkRouter with your response class
$pkRouter = new PkRouter(
  response: new CustomResponse()
);
```

```php
# ./src/Controllers/MyController.php
namespace YourNamespace\Controllers;

use Pixelkarma\PkRouter\PkRouter;

class MyController {
  private $router;

  public function __construct(PkRouter $router) {
    $this->router = $router;
  }

  public function getXml(){
    // Execute in your controller/function
    $this->router->response->sendXml($xmlData, int $code = null);
  }
}
```

# Middleware

Middleware are instances of `PkMiddlewareInterface` that can be
run before your route code and after the payload has sent.

> _Note: Middleware is executed in the order you place it in the array._

```php
new PkRoute(
  /* ... */
  before: [
    /* PkMiddlewareInterface,
       PkMiddlewareInterface */
  ],
  after: [
    /* PkMiddlewareInterface */
  ],
);
```

### Creating your own Middleware

> **Note:** If your handle() function returns `false`, it will not continue
> to the next middleware. The routing will end and `run()` will return _false_.
> If you need to pass data between middleware, consider returning an array
> or an object.

```php
namespace YourNamespace\Router\Middleware;

use Pixelkarma\PkRouter\PkMiddlewareInterface;
use Pixelkarma\PkRouter\PkRouter;

class AuthorizationMiddleware implements PkMiddlewareInterface {
  public function handle(PkRouter $router, $previous = null) {
    // Check the headers for `auth-token`
    if (false === $router->request->getHeader("auth-token", false)) {
      $router->response->sendRaw("Unauthorized", 401);
      exit; // Also acceptable to return false or throw and Exception
    }
    $router->user = YourAuthorizationCode(
                      $router->request->getHeader("auth-token")
                    );
    return true;
  }
}
```

### Passing information between Middleware

> _Note:_ Middleware returns are not passed to the callback. See [Dynamic Properties](#dynamic-properties).

```php
public function handle(PkRouter $router, $previous = 0) {
  $count = $previous + 1;
  print "Count: {$count}\n";
  return $count;
}
```

```
Count: 1
Count: 2
Count: 3
Count: 4
```

## License

MIT License, see LICENSE.md

Copyright (c) 2024 Pixel Karma, LLC <social+pkrouter@pixelkarma.com>
