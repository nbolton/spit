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
use \Spit\Models\Fields\Field as Field;
use \Spit\Models\Fields\TableField as TableField;
use \Spit\EditorMode as EditorMode;

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
    if ($this->isJsonRequest()) {
      exit($this->getJson($this->getTableData($_GET["page"], $_GET["results"])));
    }
    
    $this->showView("issues/index", T_("Issues"));
  }
  
  private function runEditor($mode) {
    if ($this->isJsonRequest()) {
      exit($this->getJson($this->getEditorFields($_GET["tracker"])));
    }
    
    switch ($mode) {
      case EditorMode::Create:
        $issue = new \Spit\Models\Issue;
        break;
        
      case EditorMode::Update:
        $id = $this->getPathPart(2);
        $issue = $this->ds->getById($id, new \Spit\CustomFields);
        break;
    }
    
    $data["saved"] = false;
    $data["mode"] = $mode;
    $data["issue"] = $issue;
    
    if ($this->isPost()) {
      $this->applyFormValues($issue);
      
      switch ($mode) {
        case EditorMode::Create:
          $this->ds->create($issue);
          break;
          
        case EditorMode::Update:
          $this->ds->update($issue);
          break;
      }
      
      $data["saved"] = true;
    }
    
    $title = ($mode == EditorMode::Create) ? T_("New Issue") : T_("Edit Issue");
    $this->showView("issues/editor", $title, $data);
  }
  
  private function runDetails() {
    $this->customFields = new \Spit\CustomFields;
    
    $id = $this->getPathPart(2);
    $issue = $this->ds->getById($id, $this->customFields);
    if ($issue == null) {
      $this->showError(404);
      return;
    }
    
    $data["columns"] = $this->getDetailColumns($issue, 2);
    $data["issue"] = $issue;
    
    $changeDataStore = new \Spit\DataStores\ChangeDataStore;
    $data["changes"] = $changeDataStore->getForIssue($id);
    
    $this->showView("issues/details", $issue->getFullTitle(), $data);
  }
  
  private function getTableData($page, $limit) {
    $start = ($page - 1) * $limit;
    $results = $this->ds->get($start, $limit, "updated", "desc");
    return array(
      "fields" => $this->getTableFields(),
      "issues" => $results[0],
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
          $field->value = $this->getFieldValue($field->name, $issue);
          array_push($column, $field);
        }
        $fieldIndex++;
      }
      array_push($columns, $column);
    }
    return $columns;
  }
  
  private function getFieldValue($fieldName, $issue) {
    $v = $issue->$fieldName;
    
    if ($v == null) {
      return sprintf("<span class=\"empty\">None</span>");
    }
    
    if ($fieldName == "creator" || $fieldName == "updater") {
      $id = ($fieldName == "creator") ? $issue->creatorId : $issue->updaterId;
      return sprintf(
        "<a href=\"%susers/details/%d/\">%s</a>",
        $this->app->getProjectRoot(), $id, $v);
    }
    
    if ($fieldName == "created" || $fieldName == "updated") {
      return $v->format("Y-m-d H:i:s");
    }
    
    $customField = $this->customFields->findFieldMapping($fieldName);
    if ($customField != null) {
      $v = $this->customFields->mapValue($customField, $v);
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
      new Field("found", T_("Found at: ")),
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
  
  private function getEditorFields($trackerId) {
  
    $fields = array();
  
    $status = new \Spit\Models\Fields\Select("status", T_("Status"));
    $status->add(T_("New"));
    $status->add(T_("Reviewed"));
    $status->add(T_("Accepted"), true);
    $status->add(T_("PatchesWelcome"));
    $status->add(T_("GotPatch"));
    $status->add(T_("InProgress"));
    $status->add(T_("Fixed"));
    $status->add(T_("Invalid"));
    $status->add(T_("Duplicate"));
    $status->add(T_("CannotReproduce"));
    array_push($fields, $status);
    
    $priority = new \Spit\Models\Fields\Select("priority", T_("Priority"));
    $priority->add(T_("Low"));
    $priority->add(T_("Normal"), true);
    $priority->add(T_("High"));
    $priority->add(T_("Urgent"));
    $priority->add(T_("Immediate"));
    array_push($fields, $priority);
    
    $version = new \Spit\Models\Fields\Select("version", T_("Version"));
    $version->add("1.4.9");
    array_push($fields, $version);
    
    if ($trackerId != 4) {
      $platform = new \Spit\Models\Fields\Select("platform", T_("Platform"));
      $platform->add("");
      $platform->add("Windows");
      $platform->add("Mac OS X");
      $platform->add("Linux");
      $platform->add("Unix");
      $platform->add("Various");
      array_push($fields, $platform);
    }
    
    $assignee = new \Spit\Models\Fields\Select("assignee", T_("Assignee"));
    $assignee->add("");
    $assignee->add("Brendon Justin");
    $assignee->add("Chris Schoeneman");
    $assignee->add("Ed Carrel");
    $assignee->add("Jason Axelson");
    $assignee->add("Jean-Sébastien Dominique");
    $assignee->add("Jodi Jones");
    $assignee->add("Nick Bolton");
    $assignee->add("Sorin Sbârnea");
    $assignee->add("Syed Amer Gilani");
    array_push($fields, $assignee);
    
    return $fields;
  }
  
  public function userCanEdit() {
    return true;
  }
}

?>
