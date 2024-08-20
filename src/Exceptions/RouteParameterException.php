<?php

namespace Pixelkarma\PkRouter\Exceptions;

use Exception;

/**
 * Class RouteParameterException
 * 
 * This exception is thrown when a route is provided with an invalid or missing parameter.
 * It extends the base Exception class and includes a default message, code,
 * and support for exception chaining.
 * 
 * @package Pixelkarma\PkRouter\Exceptions
 */
class RouteParameterException extends Exception {
  
  /**
   * RouteParameterException constructor.
   * 
   * @param string $message The Exception message to throw. Defaults to "Route parameter exception".
   * @param int $code The Exception code. Defaults to 500.
   * @param Exception|null $previous The previous exception used for exception chaining.
   */
  public function __construct($message = "Route parameter exception", $code = 500, Exception $previous = null) {
    parent::__construct($message, $code, $previous);
  }
}