<?php

namespace Spit;

require "Pages/PageFactory.class.php";

class App {
  
  const DEFAULT_PAGE = "home";
  
  public function run() {
  
    $pageName = isset($_GET["page"]) ? $_GET["page"] : self::DEFAULT_PAGE;
  
    $factory = new Pages\PageFactory;
    $page = $factory->get($pageName);
    $page->run();
  }
}

?>
