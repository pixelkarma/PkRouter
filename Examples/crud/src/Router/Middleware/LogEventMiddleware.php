<?php

namespace StorageExample\Router\Middleware;

use Pixelkarma\PkRouter\PkMiddlewareInterface;
use Pixelkarma\PkRouter\PkRouter;

class LogEventMiddleware implements PkMiddlewareInterface {
  public function handle(PkRouter $router, $previous = null) {
    // Use a response header
    $event = $router->response->getHeader("X-Request-Event");

    // The user object on Authenticated routes
    $firstName = ($router->user->firstName ?? "Unknown User");

    PkRouter::log("{$firstName} '{$event}'");

    // See CounterMiddleware.php
    return 1;
  }
}
