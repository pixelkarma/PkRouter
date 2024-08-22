<?php

namespace Pixelkarma\PkRouter\Exceptions;

/**
 * Class InvalidRouteException
 * 
 * This exception is thrown when a route is considered invalid within the PkRouter.
 * It extends the base Exception class and includes a default message, code, 
 * and support for exception chaining.
 * 
 * @package Pixelkarma\PkRouter\Exceptions
 */
class InvalidRouteException extends \Exception {
  
  /**
   * InvalidRouteException constructor.
   * 
   * @param string $message The Exception message to throw. Defaults to "Invalid route".
   * @param int $code The Exception code. Defaults to 500.
   * @param Exception|null $previous The previous exception used for exception chaining.
   */
  public function __construct($message = "Invalid route", $code = 500, \Throwable $previous = null) {
    parent::__construct($message, $code, $previous);
  }
}