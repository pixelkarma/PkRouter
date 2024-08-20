<?php

namespace Pixelkarma\PkRouter\Exceptions;

use Exception;

/**
 * Class RouterInitException
 * 
 * This exception is thrown when the router fails to initialize.
 * It extends the base Exception class and includes a default message, code,
 * and support for exception chaining.
 * 
 * @package Pixelkarma\PkRouter\Exceptions
 */
class RouterInitException extends Exception {
  
  /**
   * RouterInitException constructor.
   * 
   * @param string $message The Exception message to throw. Defaults to "Router init exception".
   * @param int $code The Exception code. Defaults to 500.
   * @param Exception|null $previous The previous exception used for exception chaining.
   */
  public function __construct($message = "Router init exception", $code = 500, Exception $previous = null) {
    parent::__construct($message, $code, $previous);
  }
}