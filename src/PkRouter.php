<?php

namespace Pixelkarma\PkRouter;

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
      if (method_exists($this, 'setup')) $this->setup();
    } catch (\Throwable $e) {
      self::log(__CLASS__ . " could not construct: " . (string) $e);
    }
  }

  final public static function log($message) {
    if (is_callable(self::$logFunction)) {
      return call_user_func(self::$logFunction, (string)$message);
    }
    error_log((string)$message);
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
      error_log(__CLASS__ . " route error: " . (string) $e);
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
    return false;
  }

  final public function run(mixed $route = null) {
    if ($route !== null) { // match() was not run first
      if ($route instanceof PkRoute) { // Was a route class sent?
        $this->route = $route;
      } else if (is_string($route)) { // Named Route?
        if (array_key_exists($route, $this->routes)) {
          $this->route = $route;
        } else {
          throw new \Exception("Route named '$route' was not found", 404);
        }
      }
    }

    // If a route has not been set by match() or above code
    // Try to look it up with match() (again?).
    if (!isset($this->route) && false === $this->match()) {
      throw new \Exception("Not Found", 404);
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
      throw new \Exception("Route callback was not found", 500);
    }

    if (isset($this->result)) {
      // After Route Addons
      if (false !== $this->executeAddons($this->route->getAddonsAfter())) return true;
    }

    return false;
  }

  private function executeAddons(array $addons) {
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
  }

  public function respond($payload, $code = null) {
    return $this->response->sendJson($payload, $code);
  }

  public function getResult() {
    return isset($this->result) ? $this->result : null;
  }
}
