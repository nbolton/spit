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
use DateTime;

use \Spit\Models\Fields\Field as Field;
use \Spit\Models\Fields\TableField as TableField;
use \Spit\EditorMode as EditorMode;
use \Spit\Models\ChangeType as ChangeType;

class IssuesController extends Controller {
  
  public function __construct() {
    $this->ds = new \Spit\DataStores\IssueDataStore;
  }
  
  public function run() {
    switch ($this->getPathPart(1)) {
      case "": $this->runIndex(); break;
      case "new": $this->runEditor(EditorMode::Create); break;
      case "edit": $this->runEditor(EditorMode::Update); break;
      case "details": $this->runDetails(); break;
      default: $this->showError(404); break;
    }
  }
  
  private function runIndex() {
    if ($this->isJsonGet()) {
      exit($this->getJson($this->getTableData($_GET["page"], $_GET["results"])));
    }
    
    $this->showView("issues/index", T_("Issues"));
  }
  
  private function runEditor($mode) {
    switch ($mode) {
      case EditorMode::Create:
        $issue = new \Spit\Models\Issue;
        break;
        
      case EditorMode::Update:
        $id = $this->getPathPart(2);
        $issue = $this->ds->getById($id, new \Spit\CustomFields);
        break;
    }
    
    if ($this->isJsonGet()) {
      exit($this->getJson($this->getEditorFields($_GET["tracker"], $issue)));
    }
    
    $data["mode"] = $mode;
    $data["issue"] = $issue;
    $data["trackerSelect"] = $this->getTrackerSelect($issue->trackerId);
    
    if ($this->isPost()) {
      $diff = $this->applyFormValues($issue);
      
      switch ($mode) {
        case EditorMode::Create:
          $issue->projectId = $this->app->project->id;
          $issue->creatorId = $this->app->user->id;
          $issue->id = $this->ds->insert($issue);
          break;
        
        case EditorMode::Update:
          $this->update($issue, $diff);
          break;
      }
      
      header(sprintf("Location: %sissues/details/%d/", $this->app->getProjectRoot(), $issue->id));
      exit;
    }
    
    $title = ($mode == EditorMode::Create) ? T_("New Issue") : T_("Edit Issue");
    $this->showView("issues/editor", $title, $data);
  }
  
  private function runDetails() {
    if ($this->isJsonPost()) {
      exit($this->getJson($this->commentPost()));
    }
    
    $this->customFields = new \Spit\CustomFields;
    
    $id = $this->getPathPart(2);
    $issue = $this->ds->getById($id, $this->customFields);
    if ($issue == null) {
      $this->showError(404);
      return;
    }
    
    $data["columns"] = $this->getDetailColumns($issue, 2);
    $data["issue"] = $issue;
    
    $cds = new \Spit\DataStores\ChangeDataStore;
    $data["changes"] = $cds->getForIssue($id);
    
    $this->showView("issues/details", $this->getIssueTitle($issue), $data, \Spit\TitleMode::Affix);
  }
  
  private function getIssueTitle($issue) {
    return sprintf("%s #%d - %s", $issue->tracker, $issue->id, $issue->title);
  }
  
  private function commentPost() {
    $change = new \Spit\Models\Change;
    $change->issueId = $this->getPathPart(2);
    $change->creatorId = $this->app->user->id;
    $change->type = \Spit\Models\ChangeType::Comment;
    $this->applyFormValues($change);
    
    $cds = new \Spit\DataStores\ChangeDataStore;
    $cds->insert($change);
    
    // values needed for "get info" functions.
    $change->created = new DateTime();
    $change->creator = $this->app->user->name;
    
    return array(
      "info" => $this->getChangeInfo($change),
      "html" => $this->getChangeContent($change)
    );
  }
  
  private function update($issue, $diff) {
    $issue->updaterId = $this->app->user->id;
    $this->ds->update($issue);
    
    foreach ($diff as $k => $v) {
      $change = new \Spit\Models\Change;
      $change->issueId = $issue->id;
      $change->creatorId = $this->app->user->id;
      $change->type = \Spit\Models\ChangeType::Edit;
      $change->name = $k;
      
      // don't store details diff for now, until we have a better diff.
      if ($k != "details") {
        $change->content = $v;
      }
      
      $cds = new \Spit\DataStores\ChangeDataStore;
      $cds->insert($change);
    }
  }
  
  private function getTableData($page, $limit) {
    $start = ($page - 1) * $limit;
    $results = $this->ds->get($start, $limit, "updated", "desc");
    
    $issues = $results[0];
    $this->replaceWithPublicValues($issues);
    
    return array(
      "fields" => $this->getTableFields(),
      "issues" => $issues,
      "pageCount" => ceil($results[1] / $limit),
    );
  }
  
  private function getDetailColumns($issue, $count) {
    $columns = array();
    $fields = $this->getTextFields();
    $totalFields = count($fields);
    $fieldsPerColumn = $totalFields / $count;
    $fieldIndex = 0;
    
    for ($i = 0; $i < $count; $i++) {
      $column = array();
      
      for ($j = 0; $j < $fieldsPerColumn; $j++) {
        if ($fieldIndex < $totalFields) {
          $field = $fields[$fieldIndex];
          
          // store value in Field object, a bit weird, but makes
          // the ajax response smaller.
          $field->value = $this->getPublicValue($field->name, $issue);
          
          array_push($column, $field);
        }
        $fieldIndex++;
      }
      array_push($columns, $column);
    }
    return $columns;
  }
  
  private function replaceWithPublicValues($issues) {
    foreach ($issues as $issue) {
      foreach ($issue as $field => $value) {
        $issue->$field = $this->getPublicValue($field, $issue, false, false, false);
      }
    }
  }
  
  // hmm... something seems not quite right about this.
  private function getPublicValue($fieldName, $issue, $empty = true, $custom = true, $users = true) {
    $v = $issue->$fieldName;
    
    if ($empty && $v == null) {
      return sprintf("<span class=\"empty\">None</span>");
    }
    
    if ($users && in_array($fieldName, array("creator", "updater", "assignee"))) {
      switch ($fieldName) {
        case "creator": $id = $issue->creatorId; break;
        case "updater": $id = $issue->updaterId; break;
        case "assignee": $id = $issue->assigneeId; break;
      }
      if ($v != null) {
        return sprintf(
          "<a href=\"%susers/details/%d/\">%s</a>",
          $this->app->getProjectRoot(), $id, $v);
      }
    }
    
    if ($fieldName == "created" || $fieldName == "updated" && $v != null) {
      return $this->formatDate($v);
    }
    
    if ($custom) {
      $customField = $this->customFields->findFieldMapping($fieldName);
      if ($customField != null) {
        $v = $this->customFields->mapValue($customField, $v);
      }
    }
    
    return $v;
  }
  
  private function getTextFields() {
    
    $fields = array(
      new Field("status", T_("Status: ")),
      new Field("priority", T_("Priority: ")),
      new Field("assignee", T_("Assignee: ")),
      new Field("category", T_("Category: ")),
      new Field("target", T_("Target: ")),
      new Field("found", T_("Found: ")),
      new Field("votes", T_("Votes: ")),
      new Field("creator", T_("Created by: ")),
      new Field("created", T_("Created on: ")),
      new Field("updater", T_("Updated by: ")),
      new Field("updated", T_("Updated on: "))
    );
    
    foreach ($this->customFields->mappings->fields as $k => $v) {
      array_push($fields, new Field($k, $v));
    }
    
    return $fields;
  }
  
  private function getTableFields() {
    
    return array(
      new TableField("tracker", T_("Tracker")),
      new TableField("status", T_("Status")),
      new TableField("priority", T_("Priority")),
      new TableField("title", T_("Title"), false, true),
      new TableField("assignee", T_("Assignee")),
      new TableField("updated", T_("Updated")),
      new TableField("votes", T_("Votes")),
    );
  }
  
  private function getTrackerSelect($trackerId) {
    $select = new \Spit\Models\Fields\Select("trackerId", T_("Tracker"));
    $dataStore = new \Spit\DataStores\TrackerDataStore;
    $this->fillSelectField($select, $dataStore->get(), $trackerId);
    return $select;
  }
  
  private function fillSelectField($select, $data, $selected) {
    foreach ($data as $item) {
      $select->add($item->id, T_($item->name), $selected == $item->id);
    }
  }
  
  private function getEditorFields($trackerId, $issue) {
  
    $fields = array();
    
    $statusDataStore = new \Spit\DataStores\StatusDataStore;
    $priorityDataStore = new \Spit\DataStores\PriorityDataStore;
    $versionDataStore = new \Spit\DataStores\VersionDataStore;
    $userDataStore = new \Spit\DataStores\UserDataStore;
  
    $status = new \Spit\Models\Fields\Select("statusId", T_("Status"));
    $this->fillSelectField($status, $statusDataStore->get(), $issue->statusId);
    array_push($fields, $status);
    
    $priority = new \Spit\Models\Fields\Select("priorityId", T_("Priority"));
    $this->fillSelectField($priority, $priorityDataStore->get(), $issue->priorityId);
    array_push($fields, $priority);
    
    $found = new \Spit\Models\Fields\Select("foundId", T_("Found"));
    $found->add(null, "");
    $this->fillSelectField($found, $versionDataStore->get(), $issue->foundId);
    array_push($fields, $found);
    
    $target = new \Spit\Models\Fields\Select("targetId", T_("Target"));
    $target->add(null, "");
    $this->fillSelectField($target, $versionDataStore->get(), $issue->targetId);
    array_push($fields, $target);
    
    $assignee = new \Spit\Models\Fields\Select("assigneeId", T_("Assignee"));
    $assignee->add(null, "");
    $this->fillSelectField($assignee, $userDataStore->get(), $issue->assigneeId);
    array_push($fields, $assignee);
    
    // TODO: load custom fields make all fields optional.
    if ($trackerId != 4) {
      $platform = new \Spit\Models\Fields\Select("platformId", T_("Platform"));
      $platform->add(null, "");
      $platform->add(1, "Windows");
      $platform->add(1, "Mac OS X");
      $platform->add(1, "Linux");
      $platform->add(1, "Unix");
      $platform->add(1, "Various");
      array_push($fields, $platform);
    }
    
    return $fields;
  }
  
  public function userCanEdit() {
    return true;
  }
  
  public function getChangeContent($change) {
    switch($change->type) {
      case \Spit\Models\ChangeType::Edit:
        return $this->getChangeEditContent($change);
      
      case \Spit\Models\ChangeType::Comment:
        return Markdown($change->content);
      
      default: return null;
    }
  }
  
  public function getChangeEditContent($change) {
    $lines = explode("\n", $change->content);
    $html = "";
    foreach ($lines as $line) {
      $class = substr($line, 0, 1) == "+" ? "add" : "remove";
      $noMarker = substr($line, 1);
      $html .= sprintf(
        "<span class=\"%s\">%s</span><br />\n", $class, $noMarker);
    }
    return sprintf("<p>%s</p>", $html);
  }
  
  public function getChangeInfo($change) {
    $date = sprintf("<i>%s</i>", $this->formatDate($change->created));
    switch($change->type) {
      case ChangeType::Edit:
        return sprintf(T_("%s: %s edited %s."), $date, $change->creator, $change->name);
      
      case ChangeType::Comment:
        return sprintf(T_("%s: %s wrote a comment."), $date, $change->creator);
    }
    
    return null;
  }
}

?>
