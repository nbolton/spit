<?php

namespace Spit\Controllers;

class Controller {

  private $master = "master";

  protected function showView($name, $data = array()) {
    foreach ($data as $k => $v) {
      $$k = $v;
    }
    
    $root = "";
    $content = "php/Views/" . $name . ".php";
    require "/php/Views/" . $this->master . ".php";
  }
}

?>
