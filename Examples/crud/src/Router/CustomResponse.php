<?php

/**
 * Example of extending PkResponse to allow for XML responses
 */

namespace StorageExample\Router;

use Pixelkarma\PkRouter\PkResponse;

class CustomResponse extends PkResponse {

  public function sendXml(array $data, int $code = 200): bool {
    $this->setHeader('Content-Type', 'application/xml');
    $payload = $this->arrayToXml($data);
    return $this->sendRaw($payload, $code);
  }

  private function arrayToXml(array $data, $rootElement = '<root>', string $xml = null): string {
    // Open the root element if not already provided in $xml
    if ($xml === null) {
      $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
      $xml .= $rootElement . "\n";
    }

    // Iterate through the array and construct the XML
    foreach ($data as $key => $value) {
      // If the key is numeric, use 'item' as the element name
      if (is_numeric($key)) {
        $key = 'item';
      }

      // If the value is an array, recursively call arrayToXml
      if (is_array($value)) {
        $xml .= "<$key>\n" . $this->arrayToXml($value, null, '') . "</$key>\n";
      } else {
        // Convert bools to string
        $value = is_bool($value) ? ($value ? 'true' : 'false') : $value;
        // Otherwise, add the element to the XML string
        $xml .= "<$key>" . htmlspecialchars($value) . "</$key>\n";
      }
    }

    // Close the root element if it's the first call
    if ($rootElement !== null) {
      $xml .= "</" . trim($rootElement, '<>') . ">\n";
    }

    return $xml;
  }
}
