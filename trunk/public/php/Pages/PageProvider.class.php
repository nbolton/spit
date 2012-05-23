<?php

namespace Spit\Pages;

require "Page.class.php";
require "HomePage.class.php";
require "IssuesPage.class.php";

use Exception;

class PageProvider {

  public function get($path) {
    if (count($path) == 0) {
      $c = new HomePage;
    }
    else {
      switch($path[0]) {
        case "issues": $c = new IssuesPage; break;
        default: throw new Exception("page not found for: " . $path[0]);
      }
    }
    return $c;
  }
}

?>
