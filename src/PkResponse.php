<?php

namespace Pixelkarma\PkRouter;

class PkResponse {
  private array $headers = [];
  private int $code = 200;

  public bool $sent = false;

  final public function __construct() {
    if (method_exists($this, 'setup')) $this->setup();
  }

  final public function setHeader(string $name, string $value = ''): bool {
    if (!trim($name)) return false;
    $this->headers[$name] = $value;
    return true;
  }

  final public function setCode(int $code): void {
    $this->code = $code;
  }

  final public function clearHeaders(): void {
    $this->headers = [];
  }

  final public function sendJson(array $payload, int $code = null): bool {
    try {
      if ($code !== null) $this->setCode($code);

      $this->setHeader("Content-Type", "application/json");

      $payload = json_encode($payload, JSON_THROW_ON_ERROR);

      $this->sendHeaders();

      print $payload;
      return true;
    } catch (\Throwable $e) {
      PkRouter::log(__CLASS__ . " sendRaw error: " . $e->getMessage());
    }
    return false;
  }

  final public function sendRaw($payload, int $code = null): bool {
    try {
      if ($code !== null) $this->setCode($code);
      $this->sendHeaders();
      print $payload;
      return true;
    } catch (\Throwable $e) {
      PkRouter::log(__CLASS__ . " sendRaw error: " . $e->getMessage());
    }
    return false;
  }

  final protected function sendHeaders(): void {
    http_response_code($this->code);
    foreach ($this->headers as $name => $value) {
      header("$name: $value");
    }
    $this->sent = true;
  }
}
