<?php

namespace Spit;

class Link {
  public $name;
  public $link;
  public function __construct($name, $link) {
    $this->name = $name;
    $this->link = $link;
  }
}

?>
