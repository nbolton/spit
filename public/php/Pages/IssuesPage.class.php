<?php

namespace Spit\Pages;

class IssuesPage extends Page {
  
  public function run($path) {
    
    if (count($path) == 1) {
      $this->showView("issues");
    }
    else {
      switch (strtolower($path[1])) {
        case "new": $this->runNew(); break;
      }
    }
  }
  
  private function runNew() {
    $this->showView("issues_new");
  }
}

?>
