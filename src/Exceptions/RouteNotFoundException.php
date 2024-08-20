<?php

namespace Pixelkarma\PkRouter\Exceptions;

use Exception;

class RouteNotFoundException extends Exception {
  public function __construct($message = "Route not found", $code = 404, Exception $previous = null) {
    parent::__construct($message, $code, $previous);
  }
}
