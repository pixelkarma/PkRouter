<?php

namespace Pixelkarma\PkRouter;

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
    $this->routeList = $this->routes();
  }

  /**
   * Retrieves the list of routes.
   *
   * @return array The array of routes defined in the application.
   */
  final public function getRoutes() {
    return $this->routeList;
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
