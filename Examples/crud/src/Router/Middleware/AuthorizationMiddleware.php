<?php

/**
 * Example Middleware for route authentication. 
 */

namespace StorageExample\Router\Middleware;

use Pixelkarma\PkRouter\PkMiddlewareInterface;
use Pixelkarma\PkRouter\PkRouter;

class AuthorizationMiddleware implements PkMiddlewareInterface {
  public function handle(PkRouter $router, $previous = null) {

    // Allow if the header contains "Authorization: EXAMPLE_KEY"
    if ($router->request->getHeader("authorization") !== "EXAMPLE_KEY") {

      // A route specific message, 
      // stored in the meta data in ./Router/MyRoutes.php
      $messageFromMeta = $router->route->getMeta("unauthorized") ?? "Unauthorized Request";

      // Also acceptable to return false or throw and exception
      $router->response->sendRaw($messageFromMeta, 401);
      exit;
    }

    // Authentication may come with user info.
    // This can be accessed with $router->user->...
    // anywhere $router is available.
    $router->user = (object)[
      "username" => "j.doe",
      "firstName" => "Joey",
      "lastName" => "Doe",
      "email" => "j.doe@domain.com"
    ];

    // Add the username to the response header:
    /*
        HTTP/1.1 200 OK
        Content-Length: 161
        Content-Type: application/json
        Host: localhost:8080
    --> x-custom-username: j.doe
        x-request-event: create
        Connection: close
    */
    $router->response->setHeader('X-Custom-Username', $router->user->username);

    return true;
  }
}
