<?php

namespace Spit;

class Plugins {

  public function __construct($app) {
    $this->app = $app;
    $this->plugins = array();
  }

  public function load() {
    $handler = opendir("php/plugins/");
    while ($file = readdir($handler)) {
      if ($file == ".." || $file == ".")
        continue;
      
      require sprintf("php/plugins/%s/%s.class.php", $file, $file);
      $plugin = new $file($this->app);
      $plugin->name = $file;
      array_push($this->plugins, $plugin);
    }
  }
}

?>
