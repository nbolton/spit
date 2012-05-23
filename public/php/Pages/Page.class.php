<?php

namespace Spit\Pages;

class Page {

  protected function showView($name, $master = "master") {
    
    $page = $name;
    require "/php/Views/" . $master . ".php";
  }
}

?>
