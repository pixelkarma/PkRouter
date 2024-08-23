<?php
require __DIR__ . '/../vendor/autoload.php';

use StorageExample\Models\StorageRepository;
use StorageExample\Router\MyRoutes;
use StorageExample\Router\CustomResponse;
use StorageExample\Router\CustomRequest;

use Pixelkarma\PkRouter\PkRouter;

// Example log function
$customLogFunction  = function ($error) {
  error_log("Custom Log function: " . (string) $error);
};

try {
  // Add the location of the storage file. Needs write access.
  StorageRepository::$storageFile = __DIR__ . "/../data/store.json";

  // Create the router with custom functionality
  $pkRouter = new PkRouter(
    routes: new MyRoutes(),
    request: new CustomRequest(),
    response: new CustomResponse(),
    logFunction: $customLogFunction
  );

  try {
    // Find a match
    if ($pkRouter->match()) {
      // Found a match, execute it.
      $pkRouter->run();
    } else {
      // Oh no, not found!
      throw new Exception("Not Found", 404);
    }
  } catch (\Throwable $e) {
    // Something along the way had an error. Fail gracefully.
    PkRouter::log($e);
    $pkRouter->respond(["success" => false, "error" => $e->getMessage() ?? "There was an error"], $e->getCode() ?? 500);
  }
} catch (\Throwable $e) {
  // Something Fatal! Respond gracefully, but only with basic functions.
  $customLogFunction($e);
  header("Content-Type: application/json");
  http_response_code(500);
  $message = $e->getPrevious() ? $e->getPrevious()->getMessage() : "Invalid Request";

  die(json_encode(["success" => false, "error" => $message]));
}
