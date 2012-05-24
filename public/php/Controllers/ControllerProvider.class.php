<?php

namespace Spit\Controllers;

require "Controller.class.php";
require "IndexController.class.php";
require "IssuesController.class.php";

use Exception;

class ControllerProvider {

  public function get($path) {
    if (count($path) == 0) {
      $c = new IndexController;
    }
    else {
      switch($path[0]) {
        case "issues": $c = new IssuesController; break;
        default: throw new Exception("page not found for: " . $path[0]);
      }
    }
    return $c;
  }
}

?>
