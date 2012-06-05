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
    $this->trackerDataStore = new \Spit\DataStores\TrackerDataStore;
    $this->versionDataStore = new \Spit\DataStores\VersionDataStore;
    $this->categoryDataStore = new \Spit\DataStores\CategoryDataStore;
    $this->issueFields = new \Spit\IssueFields($app);
  }
  
  public function redmineImport($options) {
    
    if ($options->clear) {
      $this->issueDataStore->truncate();
      $this->changeDataStore->truncate();
      $this->userDataStore->truncate();
      $this->statusDataStore->truncate();
      $this->priorityDataStore->truncate();
      $this->trackerDataStore->truncate();
      $this->versionDataStore->truncate();
      $this->categoryDataStore->truncate();
      
      // re-add current user so they aren't logged out.
      $id = $this->userDataStore->insert($this->app->security->user);
      $this->app->security->user->id = $id;
      $this->app->security->setUserId($id);
    }
    
    $context = new \stdClass;
    $context->options = $options;
    
    $db = $options->db;
    $context->redmine = new \Spit\DataStores\RedmineDataStore(
      $db->host, $db->user, $db->password, $db->name);
    
    $this->loadStatuses($context);
    $this->loadPriorities($context);
    $this->loadUsers($context);
    $this->loadIssues($context);
    $this->loadChanges($context);
    $this->loadTrackers($context);
    $this->loadVersions($context);
    $this->loadCategories($context);
    
    $this->loadCustomFieldValues($context);
    $context->customFields = $this->getCustomFieldMap($context->redmine);
    $context->customValues = $this->getCustomValueMap($context->redmine);
    
    $this->trackerDataStore->insertMany($context->trackers);
    $context->trackerIdMap = $this->getTrackerIdMap();
    
    $this->userDataStore->insertMany($context->users);
    $context->userIdMap = $this->getUserIdMap();
    
    $this->statusDataStore->insertMany($context->statuses);
    $context->statusIdMap = $this->getStatusIdMap();
    
    $this->priorityDataStore->insertMany($context->priorities);
    $context->priorityIdMap = $this->getPriorityIdMap();
    
    $this->versionDataStore->insertMany($context->versions);
    $context->versionIdMap = $this->getVersionIdMap();
    
    $this->categoryDataStore->insertMany($context->categories);
    $context->categoryIdMap = $this->getCategoryIdMap();
    
    $this->resolveIssueFields($context);
    $this->issueDataStore->insertMany($context->issues);
    $context->issueIdMap = $this->getIssueIdMap();
    
    $this->insertCustomValues($context);
    
    $this->resolveChangeFields($context);
    $this->changeDataStore->insertMany($context->changes);
  }
  
  private function insertCustomValues($context) {
    $fields = array();
    foreach ($this->issueFields->getCustomFieldMap() as $k => $v) {
      array_push($fields, $k);
    }
    
    $valueLists = array();
    foreach ($context->issues as $issue) {
      $custom = new \stdClass;
      $custom->id = $context->issueIdMap[$issue->importId];
      $custom->values = $this->getCustomValues($issue, $fields, $context);
      
      if (!$this->isEmpty($custom->values)) {
        array_push($valueLists, $custom);
      }
    }
    
    $this->issueDataStore->insertCustomMany($fields, $valueLists);
  }
  
  private function isEmpty($list) {
    foreach ($list as $item) {
      if ($item != null) {
        return false;
      }
    }
    return true;
  }
  
  private function getCustomValues($issue, $fields, $context) {
    $map = array();
    if (array_key_exists($issue->importId, $context->customValues)) {
      $values = $context->customValues[$issue->importId];
      foreach ($values as $id => $value) {
        if (array_key_exists($id, $context->options->customMap)) {
          $field = $context->options->customMap[$id];
          $valueMap = $context->customFieldValues[$field];
          
          if (count($valueMap) != 0) {
            // the field has values, so try and figure out which one it is.
            foreach ($valueMap as $possibleValue => $possibleId) {
              if ($value == $possibleValue) {
                $map[$field] = $possibleId;
              }
            }
          }
          else {
            // the field has no values, so assume that it's a plain text
            // field which doesn't use ids; just store the value.
            $map[$field] = $value;
          }
        }
      }
    }
    
    foreach ($fields as $field) {
      if (array_key_exists($field, $context->options->copyFields)) {
        $source = $context->options->copyFields[$field];
        $map[$field] = $issue->$source;
      }
    }
    
    // flatten the key value pairs to just values, and put them
    // in the same order as the fields (so the sql columns match).
    $result = array();
    foreach ($fields as $field) {
      $value = null;
      if (array_key_exists($field, $map)) {
        $value = $map[$field];
      }
      array_push($result, $value);
    }
    return $result;
  }
  
  private function loadCustomFieldValues($context) {
    $context->customFieldValues = array();
    foreach ($context->options->customMap as $redmineId => $spitId) {
      $valueMap = array();
      foreach ($this->issueFields->getCustomFieldValues($spitId) as $id => $value) {
        // used to map values to ids.
        $valueMap[$value] = $id;
      }
      $context->customFieldValues[$spitId] = $valueMap;
    }
  }
  
  private function loadIssues($context) {
    $context->issues = array();
    foreach ($context->redmine->getIssues() as $rmi) {
      $issue = new \Spit\Models\Issue;
      $issue->importId = (int)$rmi->id;
      $issue->projectId = 1;
      $issue->trackerId = (int)$rmi->tracker_id;
      $issue->statusId = (int)$rmi->status_id;
      $issue->priorityId = (int)$rmi->priority_id;
      $issue->creatorId = (int)$rmi->author_id;
      $issue->assigneeId = (int)$rmi->assigned_to_id;
      $issue->targetId = (int)$rmi->fixed_version_id;
      $issue->categoryId = (int)$rmi->category_id;
      $issue->updaterId = null;
      $issue->title = $rmi->subject;
      $issue->details = self::toMarkdown($rmi->description);
      $issue->votes = isset($rmi->votes_value) ? $rmi->votes_value : 0;
      $issue->updated = $rmi->updated_on;
      $issue->created = $rmi->created_on;
      array_push($context->issues, $issue);
    }
  }
  
  private function loadChanges($context) {
    $context->changes = array();
    foreach ($context->redmine->getJournalDetails() as $rmjd) {
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
      
      array_push($context->changes, $change);
    }
  }
  
  private function loadUsers($context) {
    $context->users = array();
    foreach ($context->redmine->getUsers() as $rmu) {
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
      
      array_push($context->users, $user);
    }
  }
  
  private function loadPriorities($context) {
    $context->priorities = array();
    $context->priorityMap = array();
    
    foreach ($context->redmine->getPriorities() as $rmp) {
      $priority = new \Spit\Models\Priority;
      $priority->importId = (int)$rmp->id;
      $priority->name = $rmp->name;
      
      array_push($context->priorities, $priority);
      $context->priorityMap[$rmp->id] = $rmp->name;
    }
  }
  
  private function loadStatuses($context) {
    $context->statuses = array();
    $context->statusMap = array();
    
    foreach ($context->redmine->getStatuses() as $rms) {
      $status = new \Spit\Models\Status;
      $status->importId = (int)$rms->id;
      $status->name = $rms->name;
      $status->closed = (bool)$rms->is_closed;
      
      array_push($context->statuses, $status);
      $context->statusMap[$rms->id] = $rms->name;
    }
  }
  
  private function loadTrackers($context) {
    $context->trackers = array();
    $context->trackerMap = array();
    
    foreach ($context->redmine->getTrackers() as $rmt) {
      $tracker = new \Spit\Models\Tracker;
      $tracker->importId = $rmt->id;
      $tracker->name = $rmt->name;
      
      array_push($context->trackers, $tracker);
      $context->trackerMap[$rmt->id] = $rmt->name;
    }
  }
  
  private function loadVersions($context) {
    $context->versions = array();
    $context->versionMap = array();
    $context->versionValueMap = array();
    
    foreach ($context->redmine->getVersions() as $rmv) {
      $version = new \Spit\Models\Version;
      $version->importId = $rmv->id;
      $version->name = $rmv->name;
      
      array_push($context->versions, $version);
      $context->versionMap[$rmv->id] = $rmv->name;
      $context->versionValueMap[$rmv->name] = $rmv->id;
    }
  }
  
  private function loadCategories($context) {
    $context->categories = array();
    $context->categoryMap = array();
    
    foreach ($context->redmine->getCategories() as $rmc) {
      $category = new \Spit\Models\Category;
      $category->importId = $rmc->id;
      $category->name = $rmc->name;
      
      array_push($context->categories, $category);
      $context->categoryMap[$rmc->id] = $rmc->name;
    }
  }
  
  private function mapFieldName($property, $prop_key) {
    if ($property == "attr") {
      switch ($prop_key) {
        case "tracker_id": return "tracker";
        case "status_id": return "status";
        case "priority_id": return "priority";
        case "category_id": return "category";
        case "subject": return "title";
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
  
  private function getTrackerIdMap() {
    $ids = $this->trackerDataStore->getImportIds();
    return $this->getImportIdMap($ids);
  }
  
  private function getVersionIdMap() {
    $ids = $this->versionDataStore->getImportIds();
    return $this->getImportIdMap($ids);
  }
  
  private function getCategoryIdMap() {
    $ids = $this->categoryDataStore->getImportIds();
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
      if ($cv->value == null) {
        continue;
      }
      
      if (array_key_exists($cv->customized_id, $map)) {
        $custom = $map[$cv->customized_id];
      }
      else {
        $custom = array();
      }
      $custom[$cv->custom_field_id] = $cv->value;
      $map[$cv->customized_id] = $custom;
    }
    return $map;
  }
  
  private function resolveIssueFields($context) {
    foreach ($context->issues as $issue) {
      $issue->creatorId = $this->getMapValue($context->userIdMap, $issue->creatorId);
      $issue->assigneeId = $this->getMapValue($context->userIdMap, $issue->assigneeId);
      $issue->statusId = $this->getMapValue($context->statusIdMap, $issue->statusId);
      $issue->priorityId = $this->getMapValue($context->priorityIdMap, $issue->priorityId);
      $issue->categoryId = $this->getMapValue($context->categoryIdMap, $issue->categoryId);
      $issue->trackerId = $this->getMapValue($context->trackerIdMap, $issue->trackerId);
      $issue->targetId = $this->getMapValue($context->versionIdMap, $issue->targetId);
      $issue->foundId = $this->findFoundId($context, $issue->importId);
    }
  }
  
  private function findFoundId($context, $redmineId) {
    if (!isset($context->options->foundIdCustom)) {
      return null;
    }
    
    // finds a version id based on the version number.
    if (array_key_exists($redmineId, $context->customValues)) {
      $values = $context->customValues[$redmineId];
      if (array_key_exists($context->options->foundIdCustom, $values)) {
        $value = $values[$context->options->foundIdCustom];
        if (array_key_exists($value, $context->versionValueMap)) {
          $redmineVersionId = $context->versionValueMap[$value];
          return $context->versionIdMap[$redmineVersionId];
        }
      }
    }
    
    return null;
  }
  
  private function resolveChangeFields($context) {
    foreach ($context->changes as $change) {
      $change->creatorId = $this->getMapValue($context->userIdMap, $change->creatorId);
      $change->issueId = $this->getMapValue($context->issueIdMap, $change->issueId);
      
      $this->resolveChangeValues($change, $context);
      
      // resolve custom field names.
      if (substr($change->name, 0, 2) == "cf") {
        $customFieldId = substr($change->name, 3);
        if (array_key_exists($customFieldId, $context->customFields)) {
          $change->name = $context->customFields[$customFieldId];
        }
      }
    }
  }
  
  private function resolveChangeValues($change, $context) {
    switch ($change->name) {
      case "status": $map = $context->statusMap; break;
      case "tracker": $map = $context->trackerMap; break;
      case "target": $map = $context->versionMap; break;
      case "category": $map = $context->categoryMap; break;
      case "priority": $map = $context->priorityMap; break;
    }
    
    if (isset($map)) {
      if (array_key_exists($change->oldValue, $map)) {
        $change->oldValue = $map[$change->oldValue];
      }
      else {
        $change->oldValue = "?";
      }
      
      if (array_key_exists($change->newValue, $map)) {
        $change->newValue = $map[$change->newValue];
      }
      else {
        $change->newValue = "?";
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
    
    foreach ($tokens as $search => $replace) {
      if ($start == $search && $end == $search) {
        return $replace . substr($line, 1, $length - 2) . $replace;
      }
    }
    
    // hashes are used sometimes when users want to write
    // a command, and also redmine counts this as numbering.
    // either way, we will just preserve the character by
    // putting a space infront (this stops it becomming a header).
    if ($start == "#") {
      return " " . $line;
    }
    
    return $line;
  }
}

?>
