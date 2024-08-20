<?php

namespace Pixelkarma\PkRouter\Exceptions;

use Exception;

class RouteAddonException extends Exception {
  public function __construct($message = "Route addon exception", $code = 500, Exception $previous = null) {
    parent::__construct($message, $code, $previous);
  }
}
