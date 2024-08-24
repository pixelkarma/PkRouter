<?php

/**
 * Example of a basic repository class for storing and 
 * modifying JSON data. 
 */

namespace StorageExample\Models;

class StorageRepository {

  public static $storageFile = "";
  private $data;

  public function __construct() {
    if (!file_exists(self::$storageFile)) throw new \Exception("Could not find storage file.", 500);
    if (!is_writable(self::$storageFile)) throw new \Exception("Storage file is not writable.", 500);
    try {
      $jsonData = json_decode(file_get_contents(self::$storageFile), true, 512, JSON_THROW_ON_ERROR);
    } catch (\JsonException $e) {
      throw new \Exception("JSON file error: " . $e->getMessage(), 500);
    }
    $this->data = $jsonData;
  }

  public function create(array $data): array {
    $record = [
      ...$data,
      "_key" => uniqid(),
    ];
    $record = $this->addRecord($record);
    $this->writeFile();
    return $record;
  }

  public function read($key = null): array {
    if ($key === null) return $this->data;
    foreach ($this->data as $record) {
      if ($record['_key'] == $key) return [$record];
    }
    return [];
  }

  public function update(string $key, array $data): array {
    $record = [
      ...$data,
      "_key" => $key,
    ];
    $record = $this->addRecord($record);
    $this->writeFile();
    return $record;
  }

  public function delete(string $key): bool {
    $this->removeKey($key);
    $this->writeFile();
    return true;
  }

  private function addRecord(array $record) {
    $this->removeKey($record["_key"]);
    foreach ($record as $key => $value) {
      if ($value == null) unset($record[$key]);
    }
    array_push($this->data, $record);
    return $record;
  }

  private function removeKey(string $key) {
    $this->data = array_filter($this->data, fn($record) => $record["_key"] !== $key);
  }

  private function writeFile() {
    try {
      $jsonData = json_encode(array_values($this->data), JSON_THROW_ON_ERROR);
      if (false === file_put_contents(self::$storageFile, $jsonData)) throw new \Exception("Could not write file.");
    } catch (\Throwable $e) {
      throw new \Exception("Write failed: " . $e->getMessage(), 500);
    }
    return true;
  }
}
