<?php

namespace Pixelkarma\PkRouter\Exceptions;

/**
 * Class BodyParserException
 * 
 * This exception is thrown when there is an issue with the request made to a route.
 * It extends the base Exception class and includes a default message, code,
 * and support for exception chaining.
 * 
 * @package Pixelkarma\PkRouter\Exceptions
 */
class BodyParserException extends \Exception {
  
  /**
   * BodyParserException constructor.
   * 
   * @param string $message The Exception message to throw. Defaults to "Body parser exception".
   * @param int $code The Exception code. Defaults to 400.
   * @param Exception|null $previous The previous exception used for exception chaining.
   */
  public function __construct($message = "Body parser exception", $code = 400, \Throwable $previous = null) {
    parent::__construct($message, $code, $previous);
  }
}