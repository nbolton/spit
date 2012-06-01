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
    if (!$this->auth(\Spit\UserType::Admin)) {
      return;
    }
    
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
    $userDataStore = new \Spit\DataStores\UserDataStore;
    
    if (isset($form->clear) && $form->clear == "on") {
      $issueDataStore->truncate();
      $changeDataStore->truncate();
      $userDataStore->truncate();
    }
    
    $users = array();
    foreach ($redmine->getUsers() as $rmu) {
      $user = new \Spit\Models\User;
      $user->importId = (int)$rmu->id;
      $user->email = $rmu->mail;
      $user->name = trim($rmu->firstname . " " . $rmu->lastname);
      array_push($users, $user);
    }
    
    $issues = array();
    foreach ($redmine->getIssues() as $rmi) {
      $issue = new \Spit\Models\Issue;
      $issue->importId = (int)$rmi->id;
      $issue->projectId = 1;
      $issue->trackerId = (int)$rmi->tracker_id;
      $issue->statusId = (int)$rmi->status_id;
      $issue->priorityId = (int)$rmi->priority_id;
      $issue->creatorId = (int)$rmi->author_id;
      $issue->assigneeId = (int)$rmi->assigned_to_id;
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
      $change->issueId = (int)$rmjd->journalized_id;
      $change->creatorId = (int)$rmjd->user_id;
      $change->type = $type;
      $change->name = $rmjd->prop_key;
      $change->content = $content;
      $change->created = $rmjd->created_on;
      array_push($changes, $change);
    }
    
    $userDataStore->insertMany($users);
    $this->resolveIssueUserIds($issues);
    $issueDataStore->insertMany($issues);
    $this->resolveChangeIssueIds($changes);
    $changeDataStore->insertMany($changes);
  }
  
  private function resolveIssueUserIds($issues) {
    $userDataStore = new \Spit\DataStores\UserDataStore;
    $ids = $userDataStore->getImportIds();
    
    $map = array();
    foreach ($ids as $idPair) {
      $map[$idPair->importId] = $idPair->id;
    }
    
    foreach ($issues as $issue) {
      $issue->creatorId = array_key_exists($issue->creatorId, $map) ? $map[$issue->creatorId] : null;
      $issue->assigneeId = array_key_exists($issue->assigneeId, $map) ? $map[$issue->assigneeId] : null;
    }
  }
  
  private function resolveChangeIssueIds($changes) {
    $issueDataStore = new \Spit\DataStores\IssueDataStore;
    $ids = $issueDataStore->getImportIds();
    
    $map = array();
    foreach ($ids as $idPair) {
      $map[$idPair->importId] = $idPair->id;
    }
    
    foreach ($changes as $change) {
      $change->issueId = array_key_exists($change->issueId, $map) ? $map[$change->issueId] : null;
    }
  }
}

?>
