<?php

namespace Pixelkarma\PkRouter;

abstract class PkRoutesConfig {
  abstract protected function setup();
  protected array $routes = [];

  final public function __construct() {
    $this->routes = $this->setup();
  }
  final public function getRoutes() {
    return $this->routes;
  }
}
