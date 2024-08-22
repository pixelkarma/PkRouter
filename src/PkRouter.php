<?php

namespace Pixelkarma\PkRouter;

use Pixelkarma\PkRouter\Exceptions\RouterInitException;
use Pixelkarma\PkRouter\Exceptions\RouteNotFoundException;
use Pixelkarma\PkRouter\Exceptions\RouteCallbackException;
use Pixelkarma\PkRouter\Exceptions\RouteCallbackNotFoundException;
use Pixelkarma\PkRouter\Exceptions\RouteMiddlewareException;
use Pixelkarma\PkRouter\Exceptions\RouterResponseException;

/**
 * Class PkRouter
 *
 * Handles routing, middleware execution, and response management for the application.
 * Provides the core functionality for defining routes, matching requests, and executing callbacks.
 *
 * @package Pixelkarma\PkRouter
 */
class PkRouter {
  /**
   * @var callable|null The logging function that can be overridden by the user.
   */
  public static $logFunction = null;

  /**
   * @var PkRoutesConfig The list of defined routes.
   */
  protected PkRoutesConfig $routes;

  /**
   * @var array A storage array for arbitrary data.
   */
  protected array $data = [];

  /**
   * @var PkRoute The currently matched route.
   */
  public PkRoute $route;

  /**
   * @var PkRequest The current request object.
   */
  public PkRequest $request;

  /**
   * @var PkResponse The current response object.
   */
  public PkResponse $response;

  /**
   * PkRouter constructor.
   *
   * @param PkRoutesConfig $routes The route configuration object.
   * @param PkRequest|null $request The request object (optional).
   * @param PkResponse|null $response The response object (optional).
   * @param callable|null $logFunction The custom log function (optional).
   * @throws RouterInitException If the router fails to initialize.
   */
  final public function __construct(
    PkRoutesConfig $routes = null,
    PkRequest $request = null,
    PkResponse $response = null,
    callable $logFunction = null
  ) {
    try {
      if ($logFunction !== null) self::$logFunction = $logFunction;
      $this->routes = $routes ?? new PkRoutesConfig();
      $this->request = $request ?? new PkRequest();
      $this->response = $response ?? new PkResponse();
    } catch (\Throwable $e) {
      self::log($e);
      throw new RouterInitException($e->getMessage() ?? "Router failed to initialize", 500, $e);
    }
  }

  /**
   * Logs errors using the provided log function or PHP's error_log if none is provided.
   *
   * @param mixed $error The error to log.
   */
  final public static function log($error) {
    if (is_callable(self::$logFunction)) {
      return call_user_func(self::$logFunction, $error);
    }
    error_log((string)$error);
  }

  /**
   * Magic method to set a property value.
   *
   * @param string $name The property name.
   * @param mixed $value The value to set.
   */
  public function __set(string $name, mixed $value) {
    $this->data[$name] = $value;
  }

  /**
   * Magic method to get a property value.
   *
   * @param string $name The property name.
   * @return mixed|null The value or null if not set.
   */
  public function __get(string $name) {
    return $this->data[$name] ?? null;
  }

  /**
   * Matches a request to a defined route.
   *
   * @param string|null $method The request method (optional).
   * @param string|null $path The request path (optional).
   * @return bool True if a matching route is found, otherwise false.
   * @throws RouteNotFoundException If no route matches the request.
   */
  final public function match(string $method = null, string $path = null) {
    $method = $method !== null ? $method : $this->request->getMethod();
    $path = $path !== null ? $path : $this->request->getPath();
    foreach ($this->routes->getRoutes() as $route) {
      $foundRoute = $route->matchRequest($method, $path);
      if ($foundRoute === true) {
        $this->route = $route;
        return true;
      }
    }
    throw new RouteNotFoundException("'$path' was not found", 404);
  }

  /**
   * Executes the matched route's callback and any associated middleware.
   *
   * @param mixed|null $route The route to execute (optional).
   * @return bool True if the route executes successfully, otherwise false.
   * @throws RouteNotFoundException If no matching route is found.
   * @throws RouteCallbackException If an error occurs in the route callback.
   */
  final public function run(mixed $route = null) {
    if ($this->route === null && $route !== null) { // match() was not run first
      if ($route instanceof PkRoute) { // Was a route class sent?
        $this->route = $route;
      } else if (is_string($route)) { // Named Route?
        if ($namedRoute = $this->routes->getRoutes($route)) {
          $this->route = $namedRoute;
        } else {
          throw new RouteNotFoundException("Named route '$route' not found", 404);
        }
      }
    }

    if (!isset($this->route) && false === $this->match()) {
      throw new RouteNotFoundException("'{$this->route->getPath()}' was not found", 404);
    }

    // Before Route Middleware
    if (false === $this->executeMiddleware($this->route->getBeforeMiddleware())) return false;

    $callback = $this->route->getCallback();

    try {
      if (is_callable($callback)) {
        // Callback is a Function
        $callback($this);
      } else if (is_array($callback) && count($callback) == 2) {
        list($controllerName, $methodName) = $callback;
        $controller = new $controllerName($this);
        call_user_func([$controller, $methodName]);
      } else {
        throw new RouteCallbackNotFoundException("Route callback was invalid", 500);
      }
    } catch (\Throwable $e) {
      self::log($e);
      throw new RouteCallbackException("Uncaught error in route callback", 500, $e);
    }

    if (false !== $this->executeMiddleware($this->route->getAfterMiddleware())) return true;

    return false;
  }

  /**
   * Executes the middleware associated with a route.
   *
   * @param array $middleware The array of middleware objects.
   * @return bool True if all middleware executes successfully, otherwise false.
   * @throws RouteMiddlewareException If an error occurs during middleware execution.
   */
  private function executeMiddleware(array $middleware) {
    if (empty($middleware)) return true;
    try {
      $middlewareResult = null;
      foreach ($middleware as $m) {
        if ($m instanceof PkMiddlewareInterface) {
          $middlewareResult = $m->handle($this, $middlewareResult);
          if (false === $middlewareResult) {
            return false;
          }
        }
      }
      return true;
    } catch (\Throwable $e) {
      self::log($e);
      throw new RouteMiddlewareException($e->getMessage() ?? "Middleware had an error", 500, $e);
    }
    return false;
  }

  /**
   * Sends a JSON response to the client.
   *
   * @param mixed $payload The data to send.
   * @param int|null $code The HTTP status code (optional).
   * @return bool True if the response is sent successfully, otherwise false.
   * @throws RouterResponseException If the router fails to send the response.
   */
  public function respond(mixed $payload, int $code = null) {
    try {
      return $this->response->sendJson($payload, $code);
    } catch (\Throwable $e) {
      self::log($e);
      throw new RouterResponseException("Router failed to respond", 500, $e);
    }
  }

  /**
   * Returns the result of the route callback execution.
   *
   * @return mixed|null The result of the callback or null if no result is set.
   */
  public function getResult() {
    return isset($this->result) ? $this->result : null;
  }
}
