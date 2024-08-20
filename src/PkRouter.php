<?php

namespace Pixelkarma\PkRouter;

use Pixelkarma\PkRouter\Exceptions\RouterInitException;
use Pixelkarma\PkRouter\Exceptions\RouteNotFoundException;
use Pixelkarma\PkRouter\Exceptions\RouteCallbackException;
use Pixelkarma\PkRouter\Exceptions\InvalidRouteException;
use Pixelkarma\PkRouter\Exceptions\RouteAddonException;
use Pixelkarma\PkRouter\Exceptions\RouterResponseException;

class PkRouter {
  public static $logFunction = null;
  protected array $routes = [];
  protected array $data = [];

  public PkRoute $route;
  public PkRequest $request;
  public PkResponse $response;

  protected $result;

  final public function __construct(
    PkRoutesConfig $routes,
    PkRequest $request = null,
    PkResponse $response = null,
    string $url = null,
    callable $logFunction = null
  ) {
    try {
      if ($logFunction !== null) self::$logFunction = $logFunction;
      $this->request = $request ?? new PkRequest($url);
      $this->response = $response ?? new PkResponse();
      $this->addRoutes($routes);
    } catch (\Throwable $e) {
      self::log($e);
      throw new RouterInitException($e->getMessage() ?? "Router failed to initialize", 500);
    }
  }

  final public static function log($error) {
    if (is_callable(self::$logFunction)) {
      return call_user_func(self::$logFunction, $error);
    }
    error_log($error);
  }

  public function __set($name, $value) {
    $this->data[$name] = $value;
  }

  public function __get($name) {
    return $this->data[$name] ?? null;
  }

  final protected function addRoute(PkRoute $route) {
    try {
      $this->routes[$route->getName()] = $route;
    } catch (\Throwable $e) {
      self::log($e);
      throw new InvalidRouteException($e->getMessage() ?? "Invalid route", 500);
    }
  }

  private function addRoutes(PkRoutesConfig $routesConfig) {
    $routes = $routesConfig->getRoutes();
    foreach ($routes as $route) {
      $this->addRoute($route);
    }
  }

  final public function match($method = null, $path = null) {
    $method = $method !== null ? $method : $this->request->getMethod();
    $path = $path !== null ? $path : $this->request->getPath();
    foreach ($this->routes as $route) {
      $foundRoute = $route->matchRequest($method, $path);
      if ($foundRoute === true) {
        $this->route = $route;
        return true;
      }
    }
    throw new RouteNotFoundException("'$path' was not found", 404);
  }

  final public function run(mixed $route = null) {
    if ($route !== null) { // match() was not run first
      if ($route instanceof PkRoute) { // Was a route class sent?
        $this->route = $route;
      } else if (is_string($route)) { // Named Route?
        if (array_key_exists($route, $this->routes)) {
          $this->route = $route;
        } else {
          throw new RouteNotFoundException("Not Found", 404);
        }
      }
    }

    // If a route has not been set by match() or above code
    // Try to look it up with match() (again?).
    if (!isset($this->route) && false === $this->match()) {
      throw new RouteNotFoundException("Route was not found", 404);
    }

    // Before Route Addons
    if (false === $this->executeAddons($this->route->getAddonsBefore())) return false;

    $callback = $this->route->getCallback();

    if (is_callable($callback)) {
      // Callback is a Function
      $this->result = $callback($this);
    } else if (false !== strpos($callback, "@")) {
      // Callback is a "Controller Action"
      list($controllerName, $methodName) = explode('@', $callback);
      $controller = new $controllerName($this);
      if (method_exists($controller, $methodName)) {
        $this->result = call_user_func([$controller, $methodName]);
      }
    } else {
      self::log(__CLASS__ . " did not find a valid callback");
      throw new RouteCallbackException("Route callback was not found", 500);
    }

    if (isset($this->result)) {
      // After Route Addons
      if (false !== $this->executeAddons($this->route->getAddonsAfter())) return true;
    }

    return false;
  }

  private function executeAddons(array $addons) {
    try {
      $addonResult = null;
      foreach ($addons as $addon) {
        if ($addon instanceof PkAddonInterface) {
          $addonResult = $addon->handle($this, $addonResult);
          if (false === $addonResult) {
            return false;
          }
        }
      }
      return true;
    } catch (\Throwable $e) {
      self::log($e);
      throw new RouteAddonException($e->getMessage() ?? "An addon had an error", 500);
    }
    return false;
  }

  public function respond($payload, $code = null) {
    try {
      return $this->response->sendJson($payload, $code);
    } catch (\Throwable $e) {
      self::log($e);
      throw new RouterResponseException("Router failed to respond", 500);
    }
  }

  public function getResult() {
    return isset($this->result) ? $this->result : null;
  }
}
