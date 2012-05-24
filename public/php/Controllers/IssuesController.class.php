<?php

namespace Spit\Controllers;

class IssuesController extends Controller {
  
  public function run($path) {
    
    if (count($path) == 1) {
      $this->showView("issues/index");
    }
    else {
      switch (strtolower($path[1])) {
        case "new": $this->runNew(); break;
      }
    }
  }
  
  private function runNew() {
    $data["editorTitle"] = "New Issue";
    $this->showView("issues/editor", $data);
  }
}

?>
