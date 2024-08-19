<?php

namespace Pixelkarma\PkRouter;

class PkRoute {
  private $matchPattern = null;

  protected string $name = "";
  protected array $methods = [];
  protected string $path = "";
  protected mixed $callback = null;
  protected array $meta = [];
  protected array $params = [];

  protected array $before = [];
  protected array $after = [];

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

  final public function __construct($route) {
    // Required
    if (!array_key_exists("name", $route) || empty($route['name']) || !is_string($route['name'])) throw new \Exception("Route 'name' string is required");
    if (!array_key_exists("methods", $route) || !is_array($route['methods'])) throw new \Exception("Route 'method' array is required");
    if (!array_key_exists("path", $route) || empty(trim($route['path'])) || !is_string($route['path'])) throw new \Exception("Route 'path' is required");
    if (!array_key_exists("callback", $route)) throw new \Exception("Route 'callback' is required");
    // Optional
    if (array_key_exists("meta", $route) && !is_array($route['meta'])) throw new \Exception("Route 'meta' must be an array");
    if (array_key_exists("before", $route) && !is_array($route['before'])) throw new \Exception("Route 'before' must be an array");
    if (array_key_exists("after", $route) && !is_array($route['after'])) throw new \Exception("Route 'after' must be an array");

    $this->name = $route['name'];
    $this->methods = $route['methods'];
    $this->path = rtrim($route['path'], '/'); // no trailing slashes for matching
    $this->callback = $route['callback'];
    $this->meta = $route['meta'] ?? [];
    $this->before = $route['before'] ?? [];
    $this->after = $route['after'] ?? [];

    if (method_exists($this, 'setup')) $this->setup();
  }

  final public static function addMatchPattern(string $key, string $pattern) {
    self::$matchOptions[$key] = $pattern;
  }

  final public function getAddonsBefore() {
    return $this->before;
  }
  final public function getAddonsAfter() {
    return $this->after;
  }
  final public function getName() {
    return $this->name;
  }
  final public function getMethods() {
    return $this->methods;
  }
  final public function getPath() {
    return $this->path;
  }
  public function getCallback() {
    return $this->callback;
  }
  final public function getMeta(string $key = null) {
    if ($key === null) return $this->meta;
    return $this->meta[$key] ?? null;
  }
  final public function getParam(string $key = null) {
    if ($key == null) return $this->params;
    return $this->params[$key] ?? null;
  }

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

  final protected function compileRegex(string $path, array $matchOptions) {
    $pattern = '/\[([' . implode("", array_keys($matchOptions)) . ']):(.*?)\]/';

    $pattern = preg_replace_callback($pattern, function ($matches) {
      $type = $matches[1];
      return $matchOptions[$type] ?? "(.*?)";
    }, $path);

    return "/^" . str_replace('/', '\/', $pattern) . "\/?$/";;
  }
}
