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

require_once "php/Spit/Models/Version.php";

class RoadmapController extends Controller {
  
  public function run() {
    switch ($this->getPathPart(1)) {
      case "": $this->runIndex(); break;
      default: $this->showError(404); break;
    }
  }
  
  private function runIndex() {
    $dataStore = new \Spit\DataStores\IssueDataStore;
    $issues = $dataStore->getForRoadmap($this->app->project->id);
    
    $versions = array();
    foreach ($issues as $issue) {
      if (array_key_exists($issue->versionId, $versions)) {
        $version = $versions[$issue->versionId];
      }
      else {
        $version = new \Spit\Models\Version;
        $version->id = $issue->versionId;
        $version->name = $issue->version;
        $version->releaseDate = $issue->versionDate;
        $version->issues = array();
        $versions[$issue->versionId] = $version;
      }
      
      $version->complete += ($issue->closed ? 1 : 0);
      
      array_push($version->issues, $issue);
    }
    
    foreach ($versions as $version) {
    }
    
    $data["versions"] = $versions;
    $this->showView("roadmap", T_("Roadmap"), $data);
  }
}

?>
