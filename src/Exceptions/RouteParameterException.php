<?php

namespace Pixelkarma\PkRouter\Exceptions;

use Exception;

class RouteParameterException extends Exception {
  public function __construct($message = "Route parameter exception", $code = 500, Exception $previous = null) {
    parent::__construct($message, $code, $previous);
  }
}
