<?php

namespace Spit\Pages;

require "Page.class.php";
require "HomePage.class.php";
require "IssuesPage.class.php";

use Exception;

class PageFactory {

  public function get($name) {
    switch($name) {
      case "home": $c = new HomePage; break;
      case "issues": $c = new IssuesPage; break;
      default: throw new Exception("page not found for: " . $name);
    }
    return $c;
  }
}

?>
