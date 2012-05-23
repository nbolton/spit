<?php

namespace Spit;

require "Pages/PageProvider.class.php";

class App {
  
  const DEFAULT_PAGE = "home";
  
  public function run() {
  
    $pathString = isset($_GET["path"]) ? $_GET["path"] : "";
    $path = preg_split('@/@', $pathString, NULL, PREG_SPLIT_NO_EMPTY);
  
    $provider = new Pages\PageProvider;
    $page = $provider->get($path);
    $page->run($path);
  }
}

?>
