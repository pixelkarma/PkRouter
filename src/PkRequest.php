<?php

namespace Pixelkarma\PkRouter;

use Pixelkarma\PkRouter\Exceptions\RouterRequestException;
use Pixelkarma\PkRouter\Exceptions\BodyParserException;

/**
 * Class PkRequest
 * 
 * This class represents an HTTP request within the PkRouter framework. It handles
 * parsing of URL, headers, body, and other related information.
 * 
 * @package Pixelkarma\PkRouter
 */
class PkRequest {

  /**
   * Content-Type constants.
   */
  const CONTENT_TYPE_JSON = 'application/json';
  const CONTENT_TYPE_FORM = 'application/x-www-form-urlencoded';
  const CONTENT_TYPE_MULTIPART = 'multipart/form-data';

  /**
   * @var array $bodyParsers Stored functions for parsing body content
   */

  private array $bodyParsers = [];

  /**
   * @var mixed $ip The client's IP address.
   */
  protected mixed $ip = null;

  /**
   * @var string $method The HTTP request method (GET, POST, etc.).
   */
  protected string $method = "";

  /**
   * @var array $cookies The cookies sent with the request.
   */
  protected array $cookies = [];

  /**
   * @var bool $secure Whether the request is over HTTPS.
   */
  protected bool $secure = false;

  /**
   * @var array $headers The request headers.
   */
  protected array $headers = [];

  /**
   * @var string $url The full URL of the request.
   */
  protected string $url = '';

  /**
   * @var array $body The parsed body of the request.
   */
  protected mixed $body = [];

  /**
   * @var array $files The uploaded files, if any.
   */
  protected array $files = [];

  /**
   * @var mixed $rawBody The raw body content of the request.
   */
  protected mixed $rawBody = "";

  /**
   * @var string $host The host of the request.
   */
  protected string $host = "";

  /**
   * @var string $path The path of the request.
   */
  protected string $path = "";

  /**
   * @var array $query The parsed query string parameters.
   */
  protected array $query = [];

  /**
   * @var string $contentType The Content-Type of the request.
   */
  protected string $contentType = "";

  /**
   * @var ?string $scheme The scheme (http or https) of the request.
   */
  protected ?string $scheme = null;

  /**
   * @var ?int $port The port of the request.
   */
  protected ?int $port = null;

  /**
   * @var ?string $user The user part of the URL, if present.
   */
  protected ?string $user = null;

  /**
   * @var ?string $pass The password part of the URL, if present.
   */
  protected ?string $pass = null;

  /**
   * @var array $params Additional parameters extracted from the URL.
   */
  public array $params = [];

  /**
   * PkRequest constructor.
   *
   * Initializes the request by parsing the URL, headers, and body.
   *
   * @throws RouterRequestException If the request cannot be properly initialized.
   */
  final public function __construct() {
    try {
      $this->rawBody = file_get_contents("php://input");
      $this->contentType = strtolower(trim($_SERVER['CONTENT_TYPE'] ?? ''));
      $this->cookies = $_COOKIE ?? [];
      $this->method = $_SERVER['REQUEST_METHOD'] ?? null;

      $this->initializeUrl();
      $this->initializeHeaders();

      $this->secure = $this->isSSL();
      $this->ip = $this->determineClientIP();

      $this->setup();

      $this->initializeBody();
    } catch (\Throwable $e) {
      throw new RouterRequestException("Request Failed", 400, $e);
    }
  }

  /**
   * Method for extending PkRequest.
   *
   * This should must be implemented by any class extending PkRequest.
   * It will be executed prior to initializing the body content to allow
   * custom body parsers and additional method creation
   *
   * @return void
   */
  protected function setup() {
  }

  /**
   * Retrieves a specific header or all headers.
   *
   * @param string|null $key The header name to retrieve, or null to retrieve all headers.
   * @param mixed $default The default value to return if the header is not found.
   * @return mixed The header value or all headers as an array.
   */
  final public function getHeader(string $key = null, $default = null) {
    if ($key === null) return $this->headers ?? [];
    return $this->headers[strtolower($key)] ?? $default;
  }

  /**
   * Retrieves a specific query parameter or all query parameters.
   *
   * @param string|null $key The query parameter name to retrieve, or null to retrieve all.
   * @param mixed $default The default value to return if the parameter is not found.
   * @return mixed The query parameter value or all query parameters as an array.
   */
  final public function getQuery(string $key = null, $default = null) {
    if ($key === null) return $this->query ?? [];
    return $this->query[$key] ?? $default;
  }

  /**
   * Retrieves a specific body parameter or all body parameters.
   *
   * @param string|null $key The body parameter name to retrieve, or null to retrieve all.
   * @param mixed $default The default value to return if the parameter is not found.
   * @return mixed The body parameter value or all body parameters as an array.
   */
  final public function getBody(string $key = null, $default = null) {
    if ($key === null) return $this->body ?? [];
    return $this->body[$key] ?? $default;
  }

  /**
   * Retrieves a specific cookie or all cookies.
   *
   * @param string|null $key The cookie name to retrieve, or null to retrieve all.
   * @param mixed $default The default value to return if the cookie is not found.
   * @return mixed The cookie value or all cookies as an array.
   */
  final public function getCookie(string $key = null, $default = null) {
    if ($key === null) return $this->cookies ?? [];
    return $this->cookies[$key] ?? $default;
  }

  /**
   * Retrieves a specific uploaded file or all files.
   *
   * @param string $key The file key to retrieve.
   * @param mixed $default The default value to return if the file is not found.
   * @return mixed The file value or all files as an array.
   */
  final public function getFile(string $key = null, $default = null) {
    if ($key == null) return ($this->files ?? []);
    return ($this->files[$key] ?? $default);
  }

  /**
   * Retrieves the HTTP method of the request.
   *
   * @return string The HTTP method.
   */
  final public function getMethod() {
    return $this->method;
  }

  /**
   * Retrieves the host of the request.
   *
   * @return string The host.
   */
  final public function getHost() {
    return $this->host;
  }

  /**
   * Retrieves the path of the request.
   *
   * @return string The path.
   */
  final public function getPath() {
    return $this->path;
  }

  /**
   * Retrieves the full URL of the request.
   *
   * @return string The full URL.
   */
  final public function getUrl() {
    return $this->url;
  }

  /**
   * Retrieves the Content-Type of the request.
   *
   * @return string The Content-Type.
   */
  final public function getContentType() {
    return $this->contentType;
  }

  /**
   * Retrieves the raw body content of the request.
   *
   * @return mixed The raw body content.
   */
  final public function getRawBody() {
    return $this->rawBody;
  }

  /**
   * Checks if the request is secure (HTTPS).
   *
   * @return bool True if the request is over HTTPS, otherwise false.
   */
  final public function isSecure() {
    return $this->secure;
  }

  /**
   * Retrieves the scheme (http or https) of the request.
   *
   * @return ?string The scheme of the request.
   */
  final public function getScheme() {
    return $this->scheme;
  }

  /**
   * Retrieves the port of the request.
   *
   * @return ?int The port of the request.
   */
  final public function getPort() {
    return $this->port;
  }

  /**
   * Retrieves the user part of the URL, if present.
   *
   * @return ?string The user part of the URL.
   */
  final public function getUser() {
    return $this->user;
  }

  /**
   * Retrieves the password part of the URL, if present.
   *
   * @return ?string The password part of the URL.
   */
  final public function getPass() {
    return $this->pass;
  }

  /**
   * Initializes the URL properties from the given or current request URL.
   */
  final protected function initializeUrl() {
    $this->url = $this->getCurrentUrl();
    $parsedUrl = parse_url($this->url);
    if (!empty($parsedUrl['scheme'])) $this->scheme = $parsedUrl['scheme'];
    if (!empty($parsedUrl['host'])) $this->host = $parsedUrl['host'];
    if (!empty($parsedUrl['port'])) $this->port = $parsedUrl['port'];
    if (!empty($parsedUrl['user'])) $this->user = $parsedUrl['user'];
    if (!empty($parsedUrl['pass'])) $this->pass = $parsedUrl['pass'];
    if (!empty($parsedUrl['path'])) $this->path = $parsedUrl['path'];
    if (!empty($parsedUrl['query'])) parse_str($parsedUrl['query'], $this->query);
  }

  /**
   * Initializes the request headers.
   */
  final protected function initializeHeaders() {
    foreach ($_SERVER as $key => $value) {
      if (strpos($key, 'HTTP_') === 0) {
        $headerName = strtolower(str_replace('_', '-', substr($key, 5)));
        $this->headers[$headerName] = $value;
      }
    }
  }

  /**
   * Initializes the request body based on the Content-Type.
   *
   * @throws \Exception If the JSON body content is invalid.
   * @throws BodyParserException If there is an exception using a custom parser.
   */
  final protected function initializeBody() {

    if (array_key_exists($this->contentType, $this->bodyParsers)) {
      try {
        $this->body = $this->bodyParsers[$this->contentType]($this->rawBody);
        return;
      } catch (\Throwable $e) {
        throw new BodyParserException("Could not parse body for '{$this->contentType}'", null, $e);
      }
    }

    if (strpos($this->contentType, self::CONTENT_TYPE_JSON) !== false) {
      $json = json_decode($this->rawBody, true, JSON_THROW_ON_ERROR);
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
      $this->body = null;
    }
  }


  /**
   * Adds additional body parsing methods for unsupported content types.
   *
   * @param string $contentType The Content-Type of the body
   * @param callable $callback A function that accepts a string $body and returns a mixed $body
   * @return void 
   */

  public function addBodyParser(string $contentType, callable $callback) {
    $this->bodyParsers[$contentType] = $callback;
  }

  /**
   * Retrieves the current request URL.
   *
   * @return string The current request URL.
   */
  final protected function getCurrentUrl() {
    $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $uri = $_SERVER['REQUEST_URI'];
    $port = $_SERVER['SERVER_PORT'];
    if (($scheme === 'http' && $port != 80) || ($scheme === 'https' && $port != 443)) {
      $host .= ':' . $port;
    }
    return $scheme . '://' . $host . ":" . $port . $uri;
  }

  /**
   * Determines the client's IP address.
   *
   * @return mixed The client's IP address.
   */
  protected function determineClientIP() {
    $forwardedFor = $this->headers['x-forwarded-for'] ?? null;
    if ($forwardedFor) {
      $ips = explode(',', $forwardedFor);
      return trim($ips[0]);
    }
    return $_SERVER['REMOTE_ADDR'] ?? null;
  }

  /**
   * Determines if the request is over HTTPS.
   *
   * @return bool True if the request is over HTTPS, otherwise false.
   */
  protected function isSSL() {
    return (
      (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ||
      (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) ||
      (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strcasecmp($_SERVER['HTTP_X_FORWARDED_PROTO'], 'https') === 0) ||
      (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on')
    );
  }
}
