<?php

/**
 * Example of extending PkRequest to allow for XML parsing
 */

namespace StorageExample\Router;

use Pixelkarma\PkRouter\PkRequest;

class CustomRequest extends PkRequest {
  // The setup() function is called by PkRequest automatically.
  protected function setup() {
    $this->addBodyParser("application/xml", [$this, 'xmlParser']);
  }

  protected function xmlParser($xmlString) {
    return json_decode(json_encode(simplexml_load_string($xmlString, "SimpleXMLElement", LIBXML_NOCDATA)), true);
  }
}
