<?php

namespace Spit\Controllers;

class Controller {

  public $app;
  
  protected function showView($name, $data = array()) {
    foreach ($data as $k => $v) {
      $$k = $v;
    }
    
    $root = "";
    $content = "php/Views/" . $name . ".php";
    $settings = $this->app->settings;
    
    require "/php/Views/" . $settings->layout->masterView . ".php";
  }
}

?>
