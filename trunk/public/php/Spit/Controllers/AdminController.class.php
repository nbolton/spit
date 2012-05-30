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

class AdminController extends Controller {

  public function run() {
    switch ($this->getPathPart(1)) {
      case "": $this->showView("admin/index", T_("Admin")); break;
      case "workflow": $this->showView("admin/workflow", T_("Workflow")); break;
      case "import": $this->runImport(); break;
      default: $this->showError(404); break;
    }
  }
  
  public function runImport() {
    if ($this->isPost()) {
      if ($_POST["app"] == "redmine") {
        $this->importFromRedmine();
      }
    }
    
    $this->showView("admin/import", T_("Import"));
  }
  
  public function importFromRedmine() {
    $db = new \stdClass();
    $this->applyFormValues($db, "db", false);
    
    $form = new \stdClass();
    $this->applyFormValues($form, null, false);
    
    $redmine = new \Spit\DataStores\RedmineDataStore(
      $db->host, $db->user, $db->password, $db->name);
    
    $issueDataStore = new \Spit\DataStores\IssueDataStore;
    if (isset($form->clear) && $form->clear == "on") {
      $issueDataStore->truncate();
    }
    
    $rmiList = $redmine->getIssues();
    $issues = array();
    
    foreach ($rmiList as $rmi) {
      $issue = new \Spit\Models\Issue;
      $issue->redmineId = $rmi->id;
      $issue->projectId = 1;
      $issue->creatorId = 1;
      $issue->title = $rmi->subject;
      $issue->details = $rmi->description;
      array_push($issues, $issue);
    }
    
    $issueDataStore->insertMany($issues);
  }
}

?>
