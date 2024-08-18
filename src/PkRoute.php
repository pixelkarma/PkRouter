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
  protected array $addons = [];

  protected static array $matchOptions = [
    'i' => "(\d+)", // Integer only
    'a' => "([a-zA-Z]+)", // Alpha only
    'n' => "([a-zA-Z0-9]+)", // AlphaNumeric
    'd' => "(\d{4}-\d{2}-\d{2})", // Date in YYYY-MM-DD format
    's' => "([a-zA-Z0-9\-._~]+)", // String all URL safe characters
    //'w' => "(.*?)", //! Wildcard -- probably unwise.
  ];

  final public function __construct($route) {
    if (!array_key_exists("name", $route) || empty($route['name'])) throw new \Exception("Route name is required");
    if (!array_key_exists("methods", $route) || !is_array($route['methods'])) throw new \Exception("Route method array is required");
    if (!array_key_exists("path", $route) || empty(trim($route['path']))) throw new \Exception("Route path is required");
    if (!array_key_exists("callback", $route)) throw new \Exception("Route callback is required");
    if (array_key_exists("meta", $route) && !is_array($route['meta'])) throw new \Exception("Route meta must be an array");
    if (array_key_exists("addons", $route) && !is_array($route['addons'])) throw new \Exception("Route addons must be an array");

    $this->name = $route['name'];
    $this->methods = $route['methods'];
    $this->path = rtrim($route['path'], '/'); // no trailing slashes for matching
    $this->callback = $route['callback'];
    $this->meta = $route['meta'] ?? [];
    $this->addons = $route['addons'] ?? [];

    if (method_exists($this, 'setup')) $this->setup();
  }

  final public static function addMatchPattern(string $letter, string $pattern) {
    self::$matchOptions[$letter] = $pattern;
  }

  final public function getAddons() {
    return $this->addons;
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
