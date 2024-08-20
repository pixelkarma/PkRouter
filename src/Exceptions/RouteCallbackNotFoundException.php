<?php

namespace Pixelkarma\PkRouter\Exceptions;

use Exception;

class RouteCallbackNotFoundException extends Exception {
  public function __construct($message = "Route callback not found", $code = 500, Exception $previous = null) {
    parent::__construct($message, $code, $previous);
  }
}
