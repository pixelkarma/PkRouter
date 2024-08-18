<?php

namespace Pixelkarma\PkRouter;

class PkRouter {
  protected array $routes = [];
  protected array $data = [];

  public PkRoute $route;
  public PkRequest $request;
  public PkResponse $response;

  final public function __construct(PkRoutesConfig $routes,  PkRequest $request = null, PkResponse $response = null, string $url = null) {
    try {
      $this->request = $request ?? new PkRequest($url);
      $this->response = $response ?? new PkResponse($url);
      $this->addRoutes($routes);
      if (method_exists($this, 'setup')) $this->setup();
    } catch (\Throwable $e) {
      error_log("PkRouter could not construct: " . (string) $e);
    }
  }

  final public function __set($name, $value) {
    $this->data[$name] = $value;
  }

  final public function __get($name) {
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

    $addons = $this->route->getAddons();
    if (!empty($addons)) {
      foreach ($addons as $addon) {
        if ($addon instanceof PkAddonInterface) {
          $addunResult = $addon->handle($this);
          if (true !== $addunResult) {
            return $addunResult;
          }
        }
      }
    }

    $callback = $this->route->getCallback();

    if (is_callable($callback)) {
      // Callback is a Function
      return $callback($this);
    } else if (false !== strpos($callback, "@")) {
      // Callback is a "Controller Action"
      list($controllerName, $methodName) = explode('@', $callback);
      $controller = new $controllerName($this);
      if (method_exists($controller, $methodName)) {
        return call_user_func([$controller, $methodName]);
      }
    }
    error_log(__CLASS__ . " did not find a valid callback");
    throw new \Exception("Route callback was not found", 500);
  }

  public function respond($payload, $code = null) {
    return $this->response->send($payload, $code);
  }
}
