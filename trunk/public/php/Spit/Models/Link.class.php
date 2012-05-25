<?php

namespace Spit;

class Link {
  public function __construct($name, $href, $external = false) {
    $this->name = $name;
    $this->href = $href;
    $this->external = $external;
  }
}

?>
