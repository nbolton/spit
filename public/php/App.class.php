<?php

namespace Spit;

require "Pages/PageFactory.class.php";

class App {
  
  const DEFAULT_PAGE = "home";
  
  public function run() {
  
    $path = isset($_GET["path"]) ? $_GET["path"] : "/";
    $parts = preg_split('@/@', $path, NULL, PREG_SPLIT_NO_EMPTY);
    $first = count($parts) != 0 ? strtolower($parts[0]) : "/";
    $pageName = $first != "/" ? $first : self::DEFAULT_PAGE;
  
    $factory = new Pages\PageFactory;
    $page = $factory->get($pageName);
    $page->run();
  }
}

?>
