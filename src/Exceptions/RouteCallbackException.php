<?php

namespace Pixelkarma\PkRouter\Exceptions;

class RouteCallbackException extends \Exception {
  public function __construct($message = "Route callback exception", $code = 500, \Throwable $previous = null) {
    parent::__construct($message, $code, $previous);
  }
}
