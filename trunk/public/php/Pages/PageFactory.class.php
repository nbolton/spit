<?php

namespace Spit\Pages;

require "Page.class.php";
require "HomePage.class.php";

use Exception;

class PageFactory {

  public function get($name) {
    switch($name) {
      case "home": $c = new HomePage; break;
      default: throw new Exception("controller not found for: " . $name);
    }
    return $c;
  }
}

?>
