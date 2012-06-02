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

namespace Spit;

class Importer {
  
  public function __construct($app) {
    $this->app = $app;
    $this->issueDataStore = new \Spit\DataStores\IssueDataStore;
    $this->changeDataStore = new \Spit\DataStores\ChangeDataStore;
    $this->userDataStore = new \Spit\DataStores\UserDataStore;
    $this->statusDataStore = new \Spit\DataStores\StatusDataStore;
  }
  
  public function redmineImport($options) {
    
    $db = $options->db;
    $redmine = new \Spit\DataStores\RedmineDataStore(
      $db->host, $db->user, $db->password, $db->name);
    
    if ($options->clear) {
      $this->issueDataStore->truncate();
      $this->changeDataStore->truncate();
      $this->userDataStore->truncate();
      $this->statusDataStore->truncate();
      
      // re-add current user so they aren't logged out.
      $id = $this->userDataStore->insert($this->app->security->user);
      $this->app->security->user->id = $id;
      $this->app->security->setUserId($id);
    }
    
    $statuses = array();
    foreach ($redmine->getStatuses() as $rms) {
      $status = new \Spit\Models\Status;
      $status->importId = (int)$rms->id;
      $status->name = $rms->name;
      $status->closed = (bool)$rms->is_closed;
      array_push($statuses, $status);
    }
    
    $users = array();
    foreach ($redmine->getUsers() as $rmu) {
      // skip user doing the import; don't add twice.
      if ($this->app->security->user->email == $rmu->mail) {
        $this->currentUserImportId = (int)$rmu->id;
        continue;
      }
      
      // skip hacky redmine users.
      if (($rmu->lastname == "Anonymous") ||
        ($rmu->firstname == "Redmine" && $rmu->lastname == "Admin")) {
        continue;
      }
      
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
      $issue->details = $this->toMarkdown($rmi->description);
      $issue->updated = $rmi->updated_on;
      $issue->created = $rmi->created_on;
      array_push($issues, $issue);
    }
    
    $changes = array();
    foreach ($redmine->getJournalDetails() as $rmjd) {
      if ($rmjd->notes != "") {
        $type = \Spit\Models\ChangeType::Comment;
        $content = $this->toMarkdown($rmjd->notes);
      }
      else {
        $type = \Spit\Models\ChangeType::Edit;
        $content = $this->app->diff($rmjd->old_value, $rmjd->value);
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
    
    $this->userDataStore->insertMany($users);
    $userIdMap = $this->getUserIdMap();
    
    $this->statusDataStore->insertMany($statuses);
    $statusIdMap = $this->getStatusIdMap();
    
    $this->resolveIssueIds($issues, $userIdMap, $statusIdMap);
    $this->issueDataStore->insertMany($issues);
    $issueIdMap = $this->getIssueIdMap();
    
    $this->resolveChangeIds($changes, $userIdMap, $issueIdMap);
    $this->changeDataStore->insertMany($changes);
  }
  
  private function getImportIdMap($ids) {
    $map = array();
    foreach ($ids as $idPair) {
      $map[$idPair->importId] = $idPair->id;
    }
    return $map;
  }
  
  private function getMapValue($map, $key) {
    return array_key_exists($key, $map) ? $map[$key] : null;;
  }
  
  private function getUserIdMap() {
    $ids = $this->userDataStore->getImportIds();
    $map = $this->getImportIdMap($ids);
    
    // map the current user's id (which was skipped during import).
    $map[$this->currentUserImportId] = $this->app->security->user->id;
    
    return $map;
  }
  
  private function getIssueIdMap() {
    $ids = $this->issueDataStore->getImportIds();
    return $this->getImportIdMap($ids);
  }
  
  private function getStatusIdMap() {
    $ids = $this->statusDataStore->getImportIds();
    return $this->getImportIdMap($ids);
  }
  
  private function resolveIssueIds($issues, $userIdMap, $statusIdMap) {
    foreach ($issues as $issue) {
      $issue->creatorId = $this->getMapValue($userIdMap, $issue->creatorId);
      $issue->assigneeId = $this->getMapValue($userIdMap, $issue->assigneeId);
      $issue->statusId = $this->getMapValue($statusIdMap, $issue->statusId);
    }
  }
  
  private function resolveChangeIds($changes, $userIdMap, $issueIdMap) {
    foreach ($changes as $change) {
      $change->creatorId = $this->getMapValue($userIdMap, $change->creatorId);
      $change->issueId = $this->getMapValue($issueIdMap, $change->issueId);
    }
  }
  
  private function toMarkdown($text) {
    $lines = preg_split("/\R/", $text);
    $result = "";
    foreach ($lines as $line) {
      
      // it's too risky to replace individual formatting chars, so only
      // replace if the char is at the start and end. this solution is
      // far from perfect, but it will do for now.
      $line = self::replaceStartAndEnd($line, array(
        "@" => "`", // monospace
        "*" => "**", // bold
      ));
      
      // redmine text formatting allows user to line break by inserting
      // a line break... markdown does not, so add this "trick" (which
      // is really a hack) to make markdown insert line breaks.
      $result .= $line . "  \n";
    }
    return $result;
  }
  
  private static function replaceStartAndEnd($line, $tokens) {
    $length = strlen($line);
    $start = substr($line, 0, 1);
    $end = substr($line, $length - 1, 1);
    
    foreach ($tokens as $search => $replace) {
      if ($start == $search && $end == $search) {
        return $replace . substr($line, 1, $length - 2) . $replace;
      }
    }
    return $line;
  }
}

?>