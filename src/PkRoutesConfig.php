<?php

namespace Pixelkarma\PkRouter;

use Pixelkarma\PkRouter\Exceptions\InvalidRouteException;

/**
 * Class PkRoutesConfig
 *
 * Abstract base class for defining and managing a list of routes.
 * Developers should extend this class and implement the `routes()` method to define their routes.
 *
 * @package Pixelkarma\PkRouter
 */
abstract class PkRoutesConfig {

  /**
   * @var array The list of routes defined in the application.
   */
  protected array $routeList = [];

  /**
   * PkRoutesConfig constructor.
   *
   * Initializes the route list by calling the abstract `routes()` method.
   */
  final public function __construct() {
    $returnedRoutesArray = $this->routes();
    if (is_array($returnedRoutesArray) && count($returnedRoutesArray)) {
      foreach ($returnedRoutesArray as $route) {
        $this->addRoute($route);
      }
    }
  }

  final public function addRoute(PkRoute $route) {

    $routeName = $route->getName();
    if (array_key_exists($routeName, $this->routeList)) {
      throw new InvalidRouteException("Duplicate route '$routeName'", 500);
    }
    $this->routeList[$routeName] = $route;
  }

  final public function getRoutes(string $key = null) {
    if ($key === null) return $this->routeList;
    return $this->routeList[$key] ?? false;
  }
  /**
   * Abstract method for defining routes.
   *
   * This method must be implemented by any class extending PkRoutesConfig.
   * It should return an array of route definitions.
   *
   * @return array The array of route definitions.
   */
  abstract protected function routes();
}
