<?php

namespace Pixelkarma\PkRouter\Exceptions;

/**
 * Class RouteMatchException
 * 
 * This exception is thrown when finding a route match. Th error is likely 
 * from an invalid regex.
 * It extends the base Exception class and includes a default message, code,
 * and support for exception chaining.
 * 
 * @package Pixelkarma\PkRouter\Exceptions
 */
class RouteMatchException extends \Exception {
  
  /**
   * RouteMatchException constructor.
   * 
   * @param string $message The Exception message to throw. Defaults to "Route match exception".
   * @param int $code The Exception code. Defaults to 500.
   * @param Exception|null $previous The previous exception used for exception chaining.
   */
  public function __construct($message = "Route match exception", $code = 500, \Throwable $previous = null) {
    parent::__construct($message, $code, $previous);
  }
}