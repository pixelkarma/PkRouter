<?php

namespace Pixelkarma\PkRouter\Exceptions;

/**
 * Class RouteNotFoundException
 * 
 * This exception is thrown when a requested route cannot be found within the PkRouter.
 * It extends the base Exception class and includes a default message, code, 
 * and support for exception chaining.
 * 
 * @package Pixelkarma\PkRouter\Exceptions
 */
class RouteNotFoundException extends \Exception {
  
  /**
   * RouteNotFoundException constructor.
   * 
   * @param string $message The Exception message to throw. Defaults to "Route not found".
   * @param int $code The Exception code. Defaults to 404.
   * @param Exception|null $previous The previous exception used for exception chaining.
   */
  public function __construct($message = "Route not found", $code = 404, \Throwable $previous = null) {
    parent::__construct($message, $code, $previous);
  }
}