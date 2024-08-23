<?php

namespace StorageExample\Controllers;

use Pixelkarma\PkRouter\PkRouter;
use StorageExample\Models\StorageRepository;


class StorageController {
  private $router;
  private $storageRepository;

  public function __construct(PkRouter $router) {
    $this->router = $router;
    $this->storageRepository = new StorageRepository();
  }

  private function sendData(bool $success, mixed $data, int $code = 200) {
    // Looking for Accept: application/xml
    if ("application/xml" == $this->router->request->getHeader("accept")) {
      // XML Support with CustomResponse.php
      return $this->router->response->sendXml(["success" => $success, "data" => $data], $code);
    } else {
      return $this->router->respond(
        ["success" => $success, "data" => $data],
        $code
      );
    }
  }

  public function create() {
    $this->router->response->setHeader("X-Request-Event", "create");

    $record = $this->router->request->getBody();
    if (count($record) == 0) return $this->sendData(false, "No Data", 500);
    $result = $this->storageRepository->create($record);
    return $this->sendData(true, $result);
  }

  public function read() {
    $this->router->response->setHeader("X-Request-Event", "read");

    $key = $this->router->route->getParam("key");
    if ($key === null) {
      $data = $this->storageRepository->read();
    } else {
      $data = $this->storageRepository->read($key);
      if (count($data) == 0) return $this->sendData(false, "Not Found", 404);
    }
    return $this->sendData(true, $data);
  }

  public function replace() {
    $this->router->response->setHeader("X-Request-Event", "replace");

    $key = $this->router->route->getParam("key");
    $record = $this->storageRepository->read($key);
    if (count($record) == 0) return $this->sendData(false, "Not Found", 404);
    $body = $this->router->request->getBody();
    if (count($body) == 0) return $this->sendData(false, "Nothing to Update", 200);
    $result = $this->storageRepository->update($key, $body);
    return $this->sendData(true, $result);
  }

  public function patch() {
    $this->router->response->setHeader("X-Request-Event", "patch");

    $key = $this->router->route->getParam("key");
    $oldRecord = $this->storageRepository->read($key);
    if (count($oldRecord) == 0) return $this->sendData(false, "Not Found", 404);
    $oldRecord = reset($oldRecord);
    $body = $this->router->request->getBody();
    if (count($body) == 0) return $this->sendData(false, "Nothing to Update", 200);
    $record = [
      ...$oldRecord,
      ...$body
    ];
    $result = $this->storageRepository->update($key, $record);
    return $this->sendData(true, $result);
  }

  public function delete() {
    $this->router->response->setHeader("X-Request-Event", "delete");

    $key = $this->router->route->getParam("key");
    $record = $this->storageRepository->read($key);
    if (count($record) == 0) return $this->sendData(false, "Not Found", 404);
    $result = $this->storageRepository->delete($key);
    return $this->sendData($result, $result ? "Deleted" : "Delete Failed");
  }
}
