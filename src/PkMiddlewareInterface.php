<?php

namespace Pixelkarma\PkRouter;

/**
 * Interface PkMiddlewareInterface
 * 
 * This interface defines the structure for middleware used within the PkRouter.
 * Middleware implementing this interface must define a `handle` method that takes a PkRouter instance 
 * and an optional previous middleware or response as parameters.
 * 
 * @package Pixelkarma\PkRouter
 */
interface PkMiddlewareInterface {

  /**
   * Handle the request and response processing.
   * 
   * @param PkRouter $router The PkRouter instance that the middleware interacts with.
   * @param mixed $previous An optional parameter that can hold the result of the previous middleware or response.
   * 
   * @return mixed The result of the middleware processing, which may be passed to the next middleware.
   */
  public function handle(PkRouter $router, $previous = null);
}