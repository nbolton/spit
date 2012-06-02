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
    $this->priorityDataStore = new \Spit\DataStores\PriorityDataStore;
  }
  
  public function redmineImport($options) {
    
    if ($options->clear) {
      $this->issueDataStore->truncate();
      $this->changeDataStore->truncate();
      $this->userDataStore->truncate();
      $this->statusDataStore->truncate();
      $this->priorityDataStore->truncate();
      
      // re-add current user so they aren't logged out.
      $id = $this->userDataStore->insert($this->app->security->user);
      $this->app->security->user->id = $id;
      $this->app->security->setUserId($id);
    }
    
    $db = $options->db;
    $redmine = new \Spit\DataStores\RedmineDataStore(
      $db->host, $db->user, $db->password, $db->name);
    
    $statuses = $this->getStatuses($redmine);
    $priorities = $this->getPriorities($redmine);
    $users = $this->getUsers($redmine);
    $issues = $this->getIssues($redmine);
    $changes = $this->getChanges($redmine);
    
    $this->userDataStore->insertMany($users);
    $userIdMap = $this->getUserIdMap();
    
    $this->statusDataStore->insertMany($statuses);
    $statusIdMap = $this->getStatusIdMap();
    
    $this->priorityDataStore->insertMany($priorities);
    $priorityIdMap = $this->getPriorityIdMap();
    
    $this->resolveIssueIds($issues, $userIdMap, $statusIdMap, $priorityIdMap);
    $this->issueDataStore->insertMany($issues);
    $issueIdMap = $this->getIssueIdMap();
    
    $this->resolveChangeIds($changes, $userIdMap, $issueIdMap);
    $this->changeDataStore->insertMany($changes);
  }
  
  private function getIssues($redmine) {
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
      $issue->details = self::toMarkdown($rmi->description);
      $issue->updated = $rmi->updated_on;
      $issue->created = $rmi->created_on;
      array_push($issues, $issue);
    }
    return $issues;
  }
  
  private function getChanges($redmine) {
    $changes = array();
    foreach ($redmine->getJournalDetails() as $rmjd) {
      $change = new \Spit\Models\Change;
      $change->issueId = (int)$rmjd->journalized_id;
      $change->creatorId = (int)$rmjd->user_id;
      $change->created = $rmjd->created_on;
      
      if ($rmjd->notes != "") {
        $change->type = \Spit\Models\ChangeType::Comment;
        $change->data = self::toMarkdown($rmjd->notes);
      }
      else if ($rmjd->property == "attachment") {
        $change->type = \Spit\Models\ChangeType::Upload;
        $change->data = $rmjd->prop_key;
      }
      else {
        $change->type = \Spit\Models\ChangeType::Edit;
        $change->name = $this->mapFieldName($rmjd->property, $rmjd->prop_key);
        $change->oldValue = $rmjd->old_value;
        $change->newValue = $rmjd->value;
      }
      
      array_push($changes, $change);
    }
    return $changes;
  }
  
  private function getUsers($redmine) {
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
    return $users;
  }
  
  private function getPriorities($redmine) {
    $priorities = array();
    foreach ($redmine->getPriorities() as $rmp) {
      $priority = new \Spit\Models\Priority;
      $priority->importId = (int)$rmp->id;
      $priority->name = $rmp->name;
      array_push($priorities, $priority);
    }
    return $priorities;
  }
  
  private function getStatuses($redmine) {
    $statuses = array();
    foreach ($redmine->getStatuses() as $rms) {
      $status = new \Spit\Models\Status;
      $status->importId = (int)$rms->id;
      $status->name = $rms->name;
      $status->closed = (bool)$rms->is_closed;
      array_push($statuses, $status);
    }
    return $statuses;
  }
  
  private function mapFieldName($property, $prop_key) {
    if ($property == "attr") {
      switch ($prop_key) {
        case "tracker_id": return "trackerId";
        case "status_id": return "statusId";
        case "description": return "details";
        default: return $prop_key;
      }
    }
    else {
      return sprintf("%s:%s", $property, $prop_key);
    }
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
  
  private function getPriorityIdMap() {
    $ids = $this->priorityDataStore->getImportIds();
    return $this->getImportIdMap($ids);
  }
  
  private function resolveIssueIds($issues, $userIdMap, $statusIdMap, $priorityIdMap) {
    foreach ($issues as $issue) {
      $issue->creatorId = $this->getMapValue($userIdMap, $issue->creatorId);
      $issue->assigneeId = $this->getMapValue($userIdMap, $issue->assigneeId);
      $issue->statusId = $this->getMapValue($statusIdMap, $issue->statusId);
      $issue->priorityId = $this->getMapValue($priorityIdMap, $issue->priorityId);
    }
  }
  
  private function resolveChangeIds($changes, $userIdMap, $issueIdMap) {
    foreach ($changes as $change) {
      $change->creatorId = $this->getMapValue($userIdMap, $change->creatorId);
      $change->issueId = $this->getMapValue($issueIdMap, $change->issueId);
    }
  }
  
  private static function toMarkdown($text) {
    $lines = preg_split("/\R/", $text);
    $result = "";
    foreach ($lines as $line) {
      // redmine text formatting allows user to line break by inserting
      // a line break... markdown does not, so add this "trick" (which
      // is really a hack) to make markdown insert line breaks.
      $result .= self::toMarkdownLine($line) . "  \n";
    }
    return $result;
  }
  
  private static function toMarkdownLine($line) {
    // it's too risky to replace individual formatting chars, so only
    // replace if the char is at the start and end. this solution is
    // far from perfect, but it will do for now.
    $tokens = array(
      "@" => "`", // monospace
      "*" => "**", // bold
    );
    
    $length = strlen($line);
    $start = substr($line, 0, 1);
    $end = substr($line, $length - 1, 1);
    
    // some users use # to indicate a command, but in markdown this
    // actually creates a header. assume the whole line is a command
    // and convert to monospace by using backticks.
    if ($start == "#") {
      return "`" . substr($line, 1, $length - 2) . "`";
    }
    
    foreach ($tokens as $search => $replace) {
      if ($start == $search && $end == $search) {
        return $replace . substr($line, 1, $length - 2) . $replace;
      }
    }
    
    return $line;
  }
}

?>
