<?php

namespace StorageExample\Router\Middleware;

use Pixelkarma\PkRouter\PkMiddlewareInterface;
use Pixelkarma\PkRouter\PkRouter;

class AuthorizationMiddleware implements PkMiddlewareInterface {
    public function handle(PkRouter $router, $previous = null) {

        // Allow if the header contains "Authorization: EXAMPLE_KEY"

        if ($router->request->getHeader("authorization") !== "EXAMPLE_KEY") {
            $messageFromMeta = $router->route->getMeta("unauthorized") ?? "Unauthorized Request";
            // Also acceptable to return false or throw and exception
            $router->response->sendRaw($messageFromMeta, 401);
            exit;
        }

        // Authentication may come with user info
        $router->user = (object)[
            "username" => "j.doe",
            "firstName" => "Joey",
            "lastName" => "Doe",
            "email" => "j.doe@domain.com"
        ];

        // Maybe we want to add that username to the response header
        $router->response->setHeader('X-Custom-Username', $router->user->username);

        return true;
    }
}
