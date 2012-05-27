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
  
  public function run() {
    switch ($this->getPathPart(1)) {
      case "": $this->runIndex(); break;
      case "new": $this->runNew(); break;
      default: $this->showError(404); break;
    }
  }
  
  private function runIndex() {
    $data["issues"] = $this->ds->get();
    $this->showView("issues/index", T_("Issues"), $data);
  }
  
  private function runNew() {
    if (isset($_GET["getFieldsFor"])) {
      exit(json_encode($this->getFieldsFor($_GET["getFieldsFor"])));
    }
    
    $data = array();
    $data["saved"] = false;
    if ($_SERVER['REQUEST_METHOD'] == "POST") {
      $issue = new \Spit\Models\Issue;
      $issue->title = $this->getPostValue("title");
      $issue->details = $this->getPostValue("details");
      $this->ds->create($issue);
      $data["saved"] = true;
    }
    
    $this->showView("issues/editor", T_("New Issue"), $data);
  }
  
  private function getFieldsFor($trackerId) {
    $status = new \Spit\Models\Fields\Select("status", T_("Status"));
    $status->add(T_("New"));
    $status->add(T_("Reviewed"));
    $status->add(T_("Accepted"));
    $status->add(T_("PatchesWelcome"));
    $status->add(T_("GotPatch"));
    $status->add(T_("InProgress"));
    $status->add(T_("Fixed"));
    $status->add(T_("Invalid"));
    $status->add(T_("Duplicate"));
    $status->add(T_("CannotReproduce"));
    
    return array(
      $status
    );
  }
}

?>
