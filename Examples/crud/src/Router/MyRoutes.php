<?php

namespace StorageExample\Router;

use StorageExample\Router\Middleware\AuthorizationMiddleware;
use StorageExample\Router\Middleware\CounterMiddleware;

use Pixelkarma\PkRouter\PkRoute;
use Pixelkarma\PkRouter\PkRoutesConfig;

class MyRoutes extends PkRoutesConfig {
  public function routes(): array {
    $authorizationMiddleware = new AuthorizationMiddleware();
    $counterMiddleware = new CounterMiddleware();

    // Example of executing an anonymous function as the callback
    $this->addRoute(new PkRoute(
      name: "home",
      path: "/",
      methods: ["GET", "POST"],
      callback: function ($router) {
        $router->response->sendRaw(
          "This is a raw response for / (root/home), Be sure to check the README.md!",
          401
        );
        return true;
      },
    ));


    // READ ALL
    $readRoute = new PkRoute(
      name: "read",
      path: "/storage/",
      methods: ["GET"],
      callback: "StorageExample\Controllers\StorageController@read",
      meta: [],
      after: [
        $counterMiddleware,
        $counterMiddleware,
        $counterMiddleware,
      ]
    );
    // READ ONE - Note the same @read function as READ ALL
    $readOneRoute = new PkRoute(
      name: "readOne",
      path: "/storage/[s:key]/",
      methods: ["GET"],
      callback: "StorageExample\Controllers\StorageController@read",
      meta: [],
      after: [
        $counterMiddleware,
        $counterMiddleware,
        $counterMiddleware,
      ]
    );
    // CREATE
    $createRoute = new PkRoute(
      name: "create",
      path: "/storage/",
      methods: ["POST"],
      callback: "StorageExample\Controllers\StorageController@create",
      meta: [
        "unauthorized" => "You are not authorized to create."
      ],
      before: [
        $authorizationMiddleware
      ],
      after: [
        $counterMiddleware,
      ]
    );
    // UPDATE (replace)
    $replaceRoute = new PkRoute(
      name: "replace",
      path: "/storage/[s:key]/",
      methods: ["POST"],
      callback: "StorageExample\Controllers\StorageController@replace",
      meta: [
        "unauthorized" => "You are not authorized to modify."
      ],
      before: [
        $authorizationMiddleware
      ],
      after: [
        $counterMiddleware,
      ]
    );
    // UPDATE (patch)
    $patchRoute = new PkRoute(
      name: "patch",
      path: "/storage/[s:key]/",
      methods: ["PATCH"],
      callback: "StorageExample\Controllers\StorageController@patch",
      meta: [
        "unauthorized" => "You are not authorized to modify."
      ],
      before: [
        $authorizationMiddleware
      ],
      after: [
        $counterMiddleware,
      ]
    );
    // DELETE
    $deleteRoute = new PkRoute(
      name: "delete",
      path: "/storage/[s:key]/",
      methods: ["DELETE"],
      callback: "StorageExample\Controllers\StorageController@delete",
      meta: [],
      before: [
        $authorizationMiddleware
      ],
      after: [
        $counterMiddleware,
      ]
    );


    return [
      // PUBLIC
      $readRoute,
      $readOneRoute,

      // AUTHORIZED
      $createRoute,
      $patchRoute,
      $deleteRoute,
      $replaceRoute,
    ];
  }
}
