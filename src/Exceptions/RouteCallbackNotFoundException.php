<?php

namespace Pixelkarma\PkRouter\Exceptions;

class RouteCallbackNotFoundException extends \Exception {
  public function __construct($message = "Route callback not found", $code = 500, \Throwable $previous = null) {
    parent::__construct($message, $code, $previous);
  }
}
