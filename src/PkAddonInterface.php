<?php

namespace Pixelkarma\PkRouter;

interface PkAddonInterface {
  public function handle(PkRouter $router, $previous = null);
}
