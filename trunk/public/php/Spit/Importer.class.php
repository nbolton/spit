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
    
    $context = new \stdClass;
    $context->statuses = $this->getStatuses($redmine);
    $context->priorities = $this->getPriorities($redmine);
    $context->users = $this->getUsers($redmine);
    $context->issues = $this->getIssues($redmine);
    $context->changes = $this->getChanges($redmine);
    
    $context->customFields = $this->getCustomFieldMap($redmine);
    $context->customValues = $this->getCustomValueMap($redmine);
    
    $this->userDataStore->insertMany($context->users);
    $context->userIdMap = $this->getUserIdMap();
    
    $this->statusDataStore->insertMany($context->statuses);
    $context->statusIdMap = $this->getStatusIdMap();
    
    $this->priorityDataStore->insertMany($context->priorities);
    $context->priorityIdMap = $this->getPriorityIdMap();
    
    $this->resolveIssueFields($context);
    $this->issueDataStore->insertMany($context->issues);
    $context->issueIdMap = $this->getIssueIdMap();
    
    $this->resolveChangeFields($context);
    $this->changeDataStore->insertMany($context->changes);
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
      $issue->votes = isset($rmi->votes_value) ? $rmi->votes_value : 0;
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
        case "tracker_id": return "tracker";
        case "status_id": return "status";
        case "priority_id": return "priority";
        case "category_id": return "category";
        case "description": return "details";
        case "fixed_version_id": return "target";
        case "assigned_to_id": return "assignee";
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
  
  private function getCustomFieldMap($redmine) {
    $map = array();
    foreach ($redmine->getCustomFields() as $cf) {
      $map[$cf->id] = $cf->name;
    }
    return $map;
  }
  
  private function getCustomValueMap($redmine) {
    $map = array();
    foreach ($redmine->getCustomValues() as $cv) {
      if (array_key_exists($cv->customized_id, $map)) {
        $custom = $map[$cv->customized_id];
      }
      else {
        $custom = array();
        $map[$cv->customized_id] = $custom;
      }
      $custom[$cv->custom_field_id] = $cv->value;
    }
    return $map;
  }
  
  private function resolveIssueFields($context) {
    foreach ($context->issues as $issue) {
      $issue->creatorId = $this->getMapValue($context->userIdMap, $issue->creatorId);
      $issue->assigneeId = $this->getMapValue($context->userIdMap, $issue->assigneeId);
      $issue->statusId = $this->getMapValue($context->statusIdMap, $issue->statusId);
      $issue->priorityId = $this->getMapValue($context->priorityIdMap, $issue->priorityId);
    }
  }
  
  private function resolveChangeFields($context) {
    foreach ($context->changes as $change) {
      $change->creatorId = $this->getMapValue($context->userIdMap, $change->creatorId);
      $change->issueId = $this->getMapValue($context->issueIdMap, $change->issueId);
      
      // resolve custom field names.
      if (substr($change->name, 0, 2) == "cf") {
        $customFieldId = substr($change->name, 3);
        if (array_key_exists($customFieldId, $context->customFields)) {
          $change->name = $context->customFields[$customFieldId];
        }
      }
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
