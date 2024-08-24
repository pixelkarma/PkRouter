<?php

namespace StorageExample\Router\Middleware;

use Pixelkarma\PkRouter\PkMiddlewareInterface;
use Pixelkarma\PkRouter\PkRouter;

class CounterMiddleware implements PkMiddlewareInterface {
  public function handle(PkRouter $router, $previous = 0) {

    // This increments a number and logs it, then passes
    // that number to the next Middleware.
    $count = $previous + 1;

    PkRouter::log("Counter = {$count}");
    return $count;
  }
}
