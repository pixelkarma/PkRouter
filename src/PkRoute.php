<?php

namespace Pixelkarma\PkRouter;

use Pixelkarma\PkRouter\Exceptions\RouteParameterException;

/**
 * Class PkRoute
 *
 * Represents a single route in the PkRouter framework, including its path, 
 * methods, callback, middleware, and other metadata.
 *
 * @package Pixelkarma\PkRouter
 */
class PkRoute {

  /**
   * @var string|null The compiled regex pattern used for matching requests.
   */
  private $matchPattern = null;

  /**
   * @var string $name The name of the route.
   */
  protected string $name = "";

  /**
   * @var array $methods The HTTP methods allowed for this route.
   */
  protected array $methods = [];

  /**
   * @var string $path The URL path for the route.
   */
  protected string $path = "";

  /**
   * @var mixed $callback The callback to be executed when the route is matched.
   */
  protected mixed $callback = null;

  /**
   * @var array $meta Additional metadata associated with the route.
   */
  protected array $meta = [];

  /**
   * @var array $params The parameters extracted from the URL when matched.
   */
  protected array $params = [];

  /**
   * @var array $before Middleware to be executed before the route callback.
   */
  protected array $before = [];

  /**
   * @var array $after Middleware to be executed after the route callback.
   */
  protected array $after = [];

  /**
   * @var array $matchOptions Default match patterns for route parameters.
   */
  protected static array $matchOptions = [
    'i' => "(\d+)", // Integer only
    'f' => "(\d+(\.\d+)?)", // Floating point number
    'a' => "([a-zA-Z]+)", // Alpha only
    'n' => "([a-zA-Z0-9]+)", // AlphaNumeric
    's' => "([a-zA-Z0-9\-._~!$&'()*+,;=:@]+)", // String with all URL-safe characters
    'e' => "([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})", // Email
    'b' => "(true|false|1|0)", // Boolean
    'ip' => "(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})", // IPv4 address
    'ipv6' => "([a-fA-F0-9:]+)", // IPv6 address
    'uu' => "([a-fA-F0-9\-]{36})", // UUID
    'uu4' => "([a-fA-F0-9]{8}-[a-fA-F0-9]{4}-4[a-fA-F0-9]{3}-[89abAB][a-fA-F0-9]{3}-[a-fA-F0-9]{12})", // UUID v4
    'ymd' => "(\d{4}-\d{2}-\d{2})", // Date in YYYY-MM-DD format
    'hms' => "([0-1][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])", // Time in HH:MM:SS format
    "*" => "(.*)" // Wildcard
  ];

  /**
   * PkRoute constructor.
   *
   * @param array $route An associative array containing route configuration.
   * @throws RouteParameterException If any required route parameters are missing or invalid.
   */
  final public function __construct($route) {
    // Required
    if (!array_key_exists("name", $route) || empty($route['name']) || !is_string($route['name'])) {
      throw new RouteParameterException("Route 'name' string is required");
    }
    if (!array_key_exists("methods", $route) || !is_array($route['methods'])) {
      throw new RouteParameterException("Route 'method' array is required");
    }
    if (!array_key_exists("path", $route) || empty(trim($route['path'])) || !is_string($route['path'])) {
      throw new RouteParameterException("Route 'path' is required");
    }
    if (!array_key_exists("callback", $route)) {
      throw new RouteParameterException("Route 'callback' is required");
    }

    // Optional
    if (array_key_exists("meta", $route) && !is_array($route['meta'])) {
      throw new RouteParameterException("Route 'meta' must be an array");
    }
    if (array_key_exists("before", $route) && !is_array($route['before'])) {
      throw new RouteParameterException("Route 'before' must be an array");
    }
    if (array_key_exists("after", $route) && !is_array($route['after'])) {
      throw new RouteParameterException("Route 'after' must be an array");
    }

    $this->name = $route['name'];
    $this->methods = $route['methods'];
    $this->path = rtrim($route['path'], '/'); // no trailing slashes for matching
    $this->callback = $route['callback'];
    $this->meta = $route['meta'] ?? [];
    $this->before = $route['before'] ?? [];
    $this->after = $route['after'] ?? [];
  }

  /**
   * Adds a custom match pattern for route parameters.
   *
   * @param string $key The key to associate with the pattern.
   * @param string $pattern The regex pattern to match.
   * @return void
   */
  final public static function addMatchPattern(string $key, string $pattern) {
    self::$matchOptions[$key] = $pattern;
  }

  /**
   * Retrieves the middleware to be executed before the route callback.
   *
   * @return array An array of middleware callbacks.
   */
  final public function getBeforeMiddleware() {
    return $this->before;
  }

  /**
   * Retrieves the middleware to be executed after the route callback.
   *
   * @return array An array of middleware callbacks.
   */
  final public function getAfterMiddleware() {
    return $this->after;
  }

  /**
   * Retrieves the name of the route.
   *
   * @return string The route name.
   */
  final public function getName() {
    return $this->name;
  }

  /**
   * Retrieves the allowed HTTP methods for this route.
   *
   * @return array An array of HTTP methods.
   */
  final public function getMethods() {
    return $this->methods;
  }

  /**
   * Retrieves the path associated with the route.
   *
   * @return string The route path.
   */
  final public function getPath() {
    return $this->path;
  }

  /**
   * Retrieves the callback associated with the route.
   *
   * @return mixed The route callback.
   */
  public function getCallback() {
    return $this->callback;
  }

  /**
   * Retrieves metadata associated with the route.
   *
   * @param string|null $key The key of the metadata to retrieve (optional).
   * @return mixed The metadata value, or the entire metadata array if no key is provided.
   */
  final public function getMeta(string $key = null) {
    if ($key === null) return $this->meta;
    return $this->meta[$key] ?? null;
  }

  /**
   * Retrieves the parameters matched in the route.
   *
   * @param string|null $key The key of the parameter to retrieve (optional).
   * @return mixed The parameter value, or the entire parameters array if no key is provided.
   */
  final public function getParam(string $key = null) {
    if ($key == null) return $this->params;
    return $this->params[$key] ?? null;
  }

  /**
   * Matches the current request against the route's path and method.
   *
   * @param string $method The HTTP method of the request.
   * @param string $requestPath The path of the request.
   * @return bool True if the request matches the route, false otherwise.
   */
  public function matchRequest(string $method, string $requestPath) {
    if (!in_array($method, $this->methods)) return false;

    if (rtrim($requestPath, '/') == $this->path) {
      return true;
    }
    if ($this->matchPattern == null) $this->matchPattern = $this->compileRegex($this->path, self::$matchOptions);

    if (preg_match($this->matchPattern, $requestPath, $matches)) {
      $params = [];
      preg_match_all('/\[.*?\:(.*?)\]/', $this->path, $paramMatches);
      foreach ($paramMatches[1] as $index => $paramName) {
        $params[$paramName] = $matches[$index + 1];
      }
      $this->params = $params;
      return true;
    }
    return false;
  }

  /**
   * Compiles the regex pattern for matching the route path.
   *
   * This method replaces placeholders in the route path with corresponding regex patterns
   * based on the match options defined in the `$matchOptions` array.
   *
   * @param string $path The route path to compile.
   * @param array $matchOptions The array of match options where keys represent the placeholder types (e.g., 'i', 'a')
   *                            and values are the corresponding regex patterns.
   * @return string The compiled regex pattern to match the route path.
   */
  final protected function compileRegex(string $path, array $matchOptions): string {
    // Build the regex pattern to find placeholders in the path (e.g., [i:id], [a:name])
    $pattern = '/\[([' . implode("", array_keys($matchOptions)) . ']):(.*?)\]/';

    // Replace each placeholder with its corresponding regex pattern from the match options
    $pattern = preg_replace_callback($pattern, function ($matches) use ($matchOptions) {
      $type = $matches[1]; // The type of the placeholder (e.g., 'i', 'a')
      return $matchOptions[$type] ?? "(.*?)"; // Use the pattern from match options or a default catch-all
    }, $path);

    // Ensure the compiled pattern matches the entire path, allowing for an optional trailing slash
    return "/^" . str_replace('/', '\/', $pattern) . "\/?$/";
  }
}
