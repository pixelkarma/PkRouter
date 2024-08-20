<?php

namespace Pixelkarma\PkRouter\Exceptions;

use Exception;

class RouteRequestException extends Exception {
  public function __construct($message = "Router request exception", $code = 500, Exception $previous = null) {
    parent::__construct($message, $code, $previous);
  }
}
