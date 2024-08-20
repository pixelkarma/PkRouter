<?php

namespace Pixelkarma\PkRouter;

use Pixelkarma\PkRouter\Exceptions\RouteRequestException;


class PkRequest {

  const CONTENT_TYPE_JSON = 'application/json';
  const CONTENT_TYPE_FORM = 'application/x-www-form-urlencoded';
  const CONTENT_TYPE_MULTIPART = 'multipart/form-data';

  protected mixed $ip = null;
  protected string $method = "";
  protected array $cookies = [];
  protected bool $secure = false;
  protected array $headers = [];
  protected string $url = '';
  protected array $body = [];
  protected array $files = [];
  protected mixed $rawBody = "";
  protected string $host = "";
  protected string $path = "";
  protected array $query = [];
  protected string $contentType = "";
  protected ?string $scheme = null;
  protected ?int $port = null;
  protected ?string $user = null;
  protected ?string $fragment = null;
  protected ?string $pass = null;

  public array $params = [];


  final public function __construct(string $url = null) {
    try {
      $this->rawBody = file_get_contents("php://input");
      $this->contentType = strtolower($_SERVER['CONTENT_TYPE'] ?? '');
      $this->cookies = $_COOKIE ?? [];
      $this->method = $_SERVER['REQUEST_METHOD'] ?? null;

      $this->initializeUrl($url);
      $this->initializeHeaders();
      $this->initializeBody();

      $this->secure = $this->isSSL();
      $this->ip = $this->determineClientIP();

    } catch (\Throwable $e) {
      throw new RouteRequestException($e->getMessage() ?? "Request construct failed");
    }
  }

  final public function getHeader(string $key = null, $default = null) {
    if ($key === null) return $this->headers ?? [];
    return $this->headers[$key] ?? $default;
  }
  final public function getQuery(string $key = null, $default = null) {
    if ($key === null) return $this->query ?? [];
    return $this->query[$key] ?? $default;
  }
  final public function getBody(string $key = null, $default = null) {
    if ($key === null) return $this->body ?? [];
    return $this->body[$key] ?? $default;
  }
  final public function getCookie(string $key = null, $default = null) {
    if ($key === null) return $this->cookies ?? [];
    return $this->cookies[$key] ?? $default;
  }
  final public function getFile(string $key = "", $default = null) {
    return $key ? ($this->files[$key] ?? $default) : ($this->files ?? []);
  }
  final public function getMethod() {
    return $this->method;
  }
  final public function getHost() {
    return $this->host;
  }
  final public function getPath() {
    return $this->path;
  }
  final public function getUrl() {
    return $this->url;
  }
  final public function getContentType() {
    return $this->contentType;
  }
  final public function getRawBody() {
    return $this->rawBody;
  }
  final  public function isSecure() {
    return $this->secure;
  }
  final public function getScheme() {
    return $this->scheme;
  }
  final public function getPort() {
    return $this->port;
  }
  final public function getUser() {
    return $this->user;
  }
  final public function getPass() {
    return $this->pass;
  }
  final public function getFragment() {
    return $this->fragment;
  }

  final protected function initializeUrl(string $url = null) {
    $this->url = $url !== null ? $url : $this->getCurrentUrl();
    $parsedUrl = parse_url($this->url);

    if (!empty($parsedUrl['scheme'])) $this->scheme = $parsedUrl['scheme'];
    if (!empty($parsedUrl['host'])) $this->host = $parsedUrl['host'];
    if (!empty($parsedUrl['port'])) $this->port = $parsedUrl['port'];
    if (!empty($parsedUrl['user'])) $this->user = $parsedUrl['user'];
    if (!empty($parsedUrl['pass'])) $this->pass = $parsedUrl['pass'];
    if (!empty($parsedUrl['path'])) $this->path = $parsedUrl['path'];
    if (!empty($parsedUrl['query'])) parse_str($parsedUrl['query'], $this->query);
    if (!empty($parsedUrl['fragment'])) $this->fragment = $parsedUrl['fragment'];
  }

  final protected function initializeHeaders() {
    foreach ($_SERVER as $key => $value) {
      if (strpos($key, 'HTTP_') === 0) {
        $headerName = strtolower(str_replace('_', '-', substr($key, 5)));
        $this->headers[$headerName] = $value;
      }
    }
  }

  final protected function initializeBody() {
    if (strpos($this->contentType, self::CONTENT_TYPE_JSON) !== false) {
      $json =  json_decode($this->rawBody, true, JSON_THROW_ON_ERROR);
      if (!is_array($json)) {
        throw new \Exception("Body content is not valid JSON", 400);
      }
      $this->body = $json;
    } elseif (strpos($this->contentType, self::CONTENT_TYPE_FORM) !== false) {
      parse_str($this->rawBody, $this->body);
    } elseif (strpos($this->contentType, self::CONTENT_TYPE_MULTIPART) !== false) {
      $this->body = $_POST;
      $this->files = $_FILES;
    } else {
      $this->body = [];
    }
  }

  final protected function getCurrentUrl() {
    $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $uri = $_SERVER['REQUEST_URI'];
    $port = $_SERVER['SERVER_PORT'];
    if (($scheme === 'http' && $port != 80) || ($scheme === 'https' && $port != 443)) {
      $host .= ':' . $port;
    }
    return $scheme . '://' . $host . $uri;
  }

  protected function determineClientIP() {
    $forwardedFor = $this->headers['x-forwarded-for'] ?? null;
    if ($forwardedFor) {
      $ips = explode(',', $forwardedFor);
      return trim($ips[0]);
    }
    return $_SERVER['REMOTE_ADDR'] ?? null;
  }

  protected function isSSL() {
    return (
      (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ||
      (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) ||
      (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strcasecmp($_SERVER['HTTP_X_FORWARDED_PROTO'], 'https') === 0) ||
      (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on')
    );
  }
}
