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

class SupportController extends Spit\Controllers\Controller {
  
  public function __construct() {
    $this->viewDir = "php/plugins/Support/views/";
  }
  
  public function run() {
    if (!$this->auth(\Spit\UserType::Newbie)) {
      return;
    }
    
    if ($this->isPost()) {
      $issue = new \Spit\Models\Issue;
      $issue->projectId = $this->app->project->id;
      $issue->creatorId = $this->app->security->user->id;
      $issue->trackerId = 3; // support
      $issue->statusId = 1; // new
      $issue->priorityId = 2; // normal
      $issue->title = $_POST["title"];
      $issue->details = $_POST["steps"];
      
      $dataStore = new \Spit\DataStores\IssueDataStore;
      $id = $dataStore->insert($issue);
      
      header("Location: " . $this->app->linkProvider->forIssue($id));
      return;
    }
    
    $this->showView("support");
  }
}

?>
