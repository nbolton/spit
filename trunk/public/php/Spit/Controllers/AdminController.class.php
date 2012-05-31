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
    $changeDataStore = new \Spit\DataStores\ChangeDataStore;
    
    if (isset($form->clear) && $form->clear == "on") {
      $issueDataStore->truncate();
      $changeDataStore->truncate();
    }
    
    $issues = array();
    foreach ($redmine->getIssues() as $rmi) {
      $issue = new \Spit\Models\Issue;
      $issue->importId = $rmi->id;
      $issue->projectId = 1;
      $issue->trackerId = $rmi->tracker_id;
      $issue->statusId = $rmi->status_id;
      $issue->priorityId = $rmi->priority_id;
      $issue->creatorId = $rmi->author_id;
      $issue->assigneeId = $rmi->assigned_to_id;
      $issue->updaterId = null;
      $issue->title = $rmi->subject;
      $issue->details = $rmi->description;
      $issue->updated = $rmi->updated_on;
      $issue->created = $rmi->created_on;
      array_push($issues, $issue);
    }
    
    $changes = array();
    foreach ($redmine->getJournalDetails() as $rmjd) {
      if ($rmjd->notes != "") {
        $type = \Spit\Models\ChangeType::Comment;
        $content = $rmjd->notes;
      }
      else {
        $type = \Spit\Models\ChangeType::Edit;
        $content = $this->diff($rmjd->old_value, $rmjd->value);
      }
      
      $change = new \Spit\Models\Change;
      $change->issueId = $rmjd->journalized_id;
      $change->creatorId = $rmjd->user_id;
      $change->type = $type;
      $change->name = $rmjd->prop_key;
      $change->content = $content;
      $change->created = $rmjd->created_on;
      array_push($changes, $change);
    }
    
    $issueDataStore->insertMany($issues);
    $this->resolveIssueIds($changes);
    $changeDataStore->insertMany($changes);
  }
  
  private function resolveIssueIds($changes) {
    $issueDataStore = new \Spit\DataStores\IssueDataStore;
    $ids = $issueDataStore->getImportIds();
    
    $map = array();
    foreach ($ids as $idPair) {
      $map[$idPair->importId] = $idPair->id;
    }
    
    foreach ($changes as $change) {
      $change->issueId = $map[$change->issueId];
    }
  }
}

?>
