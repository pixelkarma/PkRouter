<?php

namespace Pixelkarma\PkRouter\Exceptions;

use Exception;

class RouterInitException extends Exception {
  public function __construct($message = "Router init exception", $code = 500, Exception $previous = null) {
    parent::__construct($message, $code, $previous);
  }
}
