<?php

namespace Pixelkarma\PkRouter\Exceptions;

use Exception;

class InvalidRouteException extends Exception {
  public function __construct($message = "Invalid route", $code = 500, Exception $previous = null) {
    parent::__construct($message, $code, $previous);
  }
}
