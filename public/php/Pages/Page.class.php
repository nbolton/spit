<?php

namespace Spit\Pages;

class Page {

  protected function showView($name, $master = "master") {
    $root = "";
    $content = $name;
    require "/php/Views/" . $master . ".php";
  }
}

?>
