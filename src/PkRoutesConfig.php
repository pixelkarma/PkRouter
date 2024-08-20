<?php

namespace Pixelkarma\PkRouter;

abstract class PkRoutesConfig {
  abstract protected function routes();
  protected array $routeList = [];

  final public function __construct() {
    $this->routeList = $this->routes();
  }
  final public function getRoutes() {
    return $this->routeList;
  }
}
