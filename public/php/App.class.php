<?php

namespace Spit;

require "Controllers/ControllerProvider.class.php";
require "Settings.class.php";

class App {
  
  const DEFAULT_PAGE = "home";
  
  public $settings;
  
  public function __construct() {
    $this->settings = new Settings;
  }
  
  public function run() {
  
    $pathString = isset($_GET["path"]) ? $_GET["path"] : "";
    $path = preg_split('@/@', $pathString, NULL, PREG_SPLIT_NO_EMPTY);
  
    $provider = new Controllers\ControllerProvider;
    $controller = $provider->get($path);
    $controller->app = $this;
    $controller->run($path);
  }
}

?>
