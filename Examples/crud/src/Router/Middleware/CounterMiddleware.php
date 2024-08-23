<?php

namespace StorageExample\Router\Middleware;

use Pixelkarma\PkRouter\PkMiddlewareInterface;
use Pixelkarma\PkRouter\PkRouter;

class CounterMiddleware implements PkMiddlewareInterface {
  public function handle(PkRouter $router, $previous = 0) {

    // An example of passing data to the next Middleware.
    // This increments a number and logs it.
    $event = $router->response->getHeader("X-Request-Event");

    $count = $previous + 1;
    PkRouter::log("Event: $event, Count = $count");
    return $count;
  }
}
