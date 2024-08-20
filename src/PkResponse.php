<?php

namespace Pixelkarma\PkRouter;

use Pixelkarma\PkRouter\Exceptions\RouterResponseException;

/**
 * Class PkResponse
 *
 * Handles HTTP responses within the PkRouter framework, including setting headers,
 * sending JSON and raw payloads, and managing response codes.
 *
 * @package Pixelkarma\PkRouter
 */
class PkResponse {
  
  /**
   * @var array $headers The headers to be sent with the response.
   */
  private array $headers = [];

  /**
   * @var int $code The HTTP response code.
   */
  private int $code = 200;

  /**
   * @var bool $sent Flag indicating whether the response has been sent.
   */
  public bool $sent = false;

  /**
   * Sets a header to be sent with the response.
   *
   * @param string $name The name of the header.
   * @param string $value The value of the header.
   * @return bool True if the header was set, false if the header name was empty.
   */
  final public function setHeader(string $name, string $value = ''): bool {
    if (!trim($name)) return false;
    $this->headers[$name] = $value;
    return true;
  }

  /**
   * Sets the HTTP response code.
   *
   * @param int $code The HTTP response code to set.
   * @return void
   */
  final public function setCode(int $code): void {
    $this->code = $code;
  }

  /**
   * Clears all headers from the response.
   *
   * @return void
   */
  final public function clearHeaders(): void {
    $this->headers = [];
  }

  /**
   * Sends a JSON response with the given payload and optional status code.
   *
   * @param array $payload The data to be sent as a JSON response.
   * @param int|null $code The HTTP response code (optional).
   * @return bool|string The JSON encoded payload if successful, false otherwise.
   * @throws RouterResponseException If an error occurs while encoding or sending the response.
   */
  final public function sendJson(array $payload, int $code = null): bool|string {
    try {
      if ($code !== null) $this->setCode($code);

      $this->setHeader("Content-Type", "application/json");

      $payload = json_encode($payload, JSON_THROW_ON_ERROR);

      $this->sendHeaders();

      print $payload;
      return $payload;
    } catch (\Throwable $e) {
      PkRouter::log($e);
      throw new RouterResponseException($e->getMessage() ?? "Response failed", 500);
    }
    return false;
  }

  /**
   * Sends a raw response with the given payload and optional status code.
   *
   * @param mixed $payload The raw data to be sent in the response.
   * @param int|null $code The HTTP response code (optional).
   * @return bool True if the response was sent successfully, false otherwise.
   * @throws RouterResponseException If an error occurs while sending the response.
   */
  final public function sendRaw($payload, int $code = null): bool {
    try {
      if ($code !== null) $this->setCode($code);
      $this->sendHeaders();
      print $payload;
      return true;
    } catch (\Throwable $e) {
      PkRouter::log($e);
      throw new RouterResponseException($e->getMessage() ?? "Response failed", 500);
    }
    return false;
  }

  /**
   * Sends the HTTP headers.
   *
   * @return void
   */
  final protected function sendHeaders(): void {
    http_response_code($this->code);
    foreach ($this->headers as $name => $value) {
      header("$name: $value");
    }
    $this->sent = true;
  }
}