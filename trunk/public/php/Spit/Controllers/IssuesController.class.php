<?php

/*
 * SPIT: Simple PHP Issue Tracker
 * Copyright (C) 2012 Nick Bolton
 * 
 * This package is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * found in the file COPYING that should have accompanied this file.
 * 
 * This package is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Spit\Controllers;

use Exception;

class IssuesController extends Controller {
  
  public function __construct() {
    $this->ds = new \Spit\DataStores\IssueDataStore;
  }
  
  public function run($path) {
    if (count($path) == 1) {
      $this->runIndex();
    }
    else {
      switch (strtolower($path[1])) {
        case "new": $this->runNew(); break;
        default: throw new Exception("unknown path: " . $path[1]);
      }
    }
  }
  
  public function runIndex() {
    $this->title = T_("Issues");
    $data["issues"] = $this->ds->get();
    $this->showView("issues/index", $data);
  }
  
  private function runNew() {
    $this->title = T_("New Issue");
    
    $data = array();
    $data["saved"] = false;
    if ($_SERVER['REQUEST_METHOD'] == "POST") {
      $issue = new \Spit\Models\Issue;
      $issue->title = $this->getPostValue("title");
      $issue->details = $this->getPostValue("details");
      $this->ds->create($issue);
      $data["saved"] = true;
    }
    
    $this->showView("issues/editor", $data);
  }
}

?>
