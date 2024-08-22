<?php

namespace Pixelkarma\PkRouter\Exceptions;

/**
 * Class RouteMiddlewareException
 * 
 * This exception is thrown when an unhandled error occurs in a route middleware within the PkRouter.
 * It extends the base Exception class and includes a default message, code,
 * and support for exception chaining.
 * 
 * @package Pixelkarma\PkRouter\Exceptions
 */
class RouteMiddlewareException extends \Exception {
  
  /**
   * RouteMiddlewareException constructor.
   * 
   * @param string $message The Exception message to throw. Defaults to "Route middleware exception".
   * @param int $code The Exception code. Defaults to 500.
   * @param Exception|null $previous The previous exception used for exception chaining.
   */
  public function __construct($message = "Route middleware exception", $code = 500, \Throwable $previous = null) {
    parent::__construct($message, $code, $previous);
  }
}