<?php

namespace Pixelkarma\PkRouter\Exceptions;

use Exception;

class RouterResponseException extends Exception {
  public function __construct($message = "Route response exception", $code = 500, Exception $previous = null) {
    parent::__construct($message, $code, $previous);
  }
}
