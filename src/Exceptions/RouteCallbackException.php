<?php

namespace Pixelkarma\PkRouter\Exceptions;

use Exception;

class RouteCallbackException extends Exception {
  public function __construct($message = "Route callback exception", $code = 500, Exception $previous = null) {
    parent::__construct($message, $code, $previous);
  }
}
