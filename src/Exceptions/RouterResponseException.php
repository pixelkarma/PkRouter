<?php

namespace Pixelkarma\PkRouter\Exceptions;

use Exception;

/**
 * Class RouterResponseException
 * 
 * This exception is thrown when there is an issue with the response handling in the router.
 * It extends the base Exception class and includes a default message, code,
 * and support for exception chaining.
 * 
 * @package Pixelkarma\PkRouter\Exceptions
 */
class RouterResponseException extends Exception {
  
  /**
   * RouterResponseException constructor.
   * 
   * @param string $message The Exception message to throw. Defaults to "Route response exception".
   * @param int $code The Exception code. Defaults to 500.
   * @param Exception|null $previous The previous exception used for exception chaining.
   */
  public function __construct($message = "Route response exception", $code = 500, Exception $previous = null) {
    parent::__construct($message, $code, $previous);
  }
}