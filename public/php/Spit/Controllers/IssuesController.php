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

require_once "php/markdown/markdown.php";

require_once "php/Spit/EditorMode.php";
require_once "php/Spit/IssueFields.php";
require_once "php/Spit/ChangeResolver.php";

require_once "php/Spit/DataStores/IssueDataStore.php";
require_once "php/Spit/DataStores/QueryDataStore.php";
require_once "php/Spit/DataStores/TrackerDataStore.php";
require_once "php/Spit/DataStores/StatusDataStore.php";
require_once "php/Spit/DataStores/PriorityDataStore.php";
require_once "php/Spit/DataStores/VersionDataStore.php";
require_once "php/Spit/DataStores/CategoryDataStore.php";
require_once "php/Spit/DataStores/ChangeDataStore.php";
require_once "php/Spit/DataStores/RelationDataStore.php";
require_once "php/Spit/DataStores/AttachmentDataStore.php";

require_once "php/Spit/Models/Fields/DisplayField.php";
require_once "php/Spit/Models/Fields/TextField.php";
require_once "php/Spit/Models/Fields/SelectField.php";

use Exception;
use DateTime;

use \Spit\Models\Fields\Field as Field;
use \Spit\Models\Fields\TableField as TableField;
use \Spit\Models\Fields\SelectField as SelectField;
use \Spit\Models\Fields\TextField as TextField;
use \Spit\Models\Fields\DisplayField as DisplayField;
use \Spit\EditorMode as EditorMode;
use \Spit\Models\ChangeType as ChangeType;
use \Spit\Models\RelationType as RelationType;
use \Spit\Models\Query as Query;

// TODO: this class has become bloated;
// favour fat models and skinny controllers.

class IssuesController extends Controller {
  
  public function __construct() {
    $this->ds = new \Spit\DataStores\IssueDataStore;
    $this->qds = new \Spit\DataStores\QueryDataStore;
  }
  
  public function run() {
    $part = $this->getPathPart(1);
    switch ($part) {
      case "": $this->runIndex(); break;
      case "new": $this->runEditor(EditorMode::Create); break;
      case "edit": $this->runEditor(EditorMode::Update); break;
      case "details": $this->runDetails(); break;
      default: {
        $query = $this->qds->getByName($part, $this->app->project->id);
        if ($query != null) {
          $this->runIndex($query);
        }
        else {
          $this->showError(404);
        }
        break;
      }
    }
  }
  
  private function runIndex($query = null) {
    $data["query"] = $query;
    
    if ($this->isJsonGet()) {
      exit($this->getJson($this->getTableData($_GET["page"], $_GET["results"], $query)));
    }
    
    if ($this->isPost()) {
      if (isset($_POST["query"])) {
        $this->handleQueryPost();
        return;
      }
    }
    
    $data["queries"] = $this->qds->get($this->app->project->id);
    
    $this->showView("issues/index", T_("Issues"), $data);
  }
  
  private function runEditor($mode) {
    $this->useMarkdown = true;
  
    $lp = $this->app->linkProvider;
    
    switch ($mode) {
      case EditorMode::Create: {
        $issue = new \Spit\Models\Issue;
        
        // TODO: allow different template for each tracker.
        $filename = "issue.txt";
        if (file_exists($filename)) {
          $handle = fopen($filename, "r");
          $contents = fread($handle, filesize($filename));
          fclose($handle);
          $issue->details = $contents;
        }
        
        if (!$this->userCanCreate()) {
          return;
        }
        $lp->securityRedirect = $lp->forIssueIndex();
        break;
      }
        
      case EditorMode::Update: {
        $id = $this->getPathPart(2);
        $lp->securityRedirect = $lp->forIssue($id);
        $issueFields = new \Spit\IssueFields($this->app->project->name);
        $issue = $this->ds->getById($id, $this->app->project->id, $issueFields);
        if (!$this->userCanEdit($issue)) {
          return;
        }
        break;
      }
    }
    
    if ($this->isJsonGet()) {
      if (isset($_GET["tracker"])) {
        exit($this->getJson($this->getEditorFields($_GET["tracker"], $issue)));
      }
      else if (isset($_GET["search"])) {
        exit($this->getJson($this->search($_GET["search"])));
      }
      exit;
    }
    
    $data["mode"] = $mode;
    $data["issue"] = $issue;
    $data["trackerSelect"] = $this->getTrackerSelect($issue->trackerId);
    
    if ($this->isPost()) {
      $diff = $this->applyFormValues($issue);
      
      $issueFields = new \Spit\IssueFields($this->app->project->name);
      
      // TODO: validation can only run if the user is an advanced editor,
      // since only title and description is only available for basic edit,
      // which is validated by the javascript.
      if ($this->userCanEditAdvanced()) {
        $validateResult = $issueFields->validate($issue, $issue->trackerId);
        if (count($validateResult->invalid) != 0) {
          var_dump($validateResult);
          exit;
        }
      }
      
      switch ($mode) {
        case EditorMode::Create:
          // HACK: massive hack, hard coded database ids, ugh... this is very
          // temporary and will definitely screw someone over. this must be
          // corrected asap!!!
          if (!$this->userCanEditAdvanced()) {
            $issue->trackerId = 3; // support
            $issue->statusId = 1; // new
            $issue->priorityId = 2; // normal
          }
          $this->insert($issue, $diff, $issueFields);
          break;
        
        case EditorMode::Update:
          if (count($diff) != 0) {
            $this->update($issue, $diff, $issueFields);
          }
          break;
      }
      
      $query = isset($_GET["query"]) ? $_GET["query"] : null;
      header("Location: " . $this->app->linkProvider->forIssue($issue->id, $query));
      exit;
    }
    
    $title = ($mode == EditorMode::Create) ? T_("New Issue") : T_("Edit Issue");
    $this->showView("issues/editor", $title, $data);
  }
  
  private function runDetails() {
    
    if ($this->isJsonPost()) {
      if (isset($_GET["comment"])) {
        exit($this->getJson($this->commentPost()));
      }
      if (isset($_GET["createRelation"])) {
        exit($this->getJson($this->createRelation()));
      }
      exit;
    }
    
    if ($this->isJsonGet() && isset($_GET["deleteRelation"])) {
      exit($this->getJson($this->deleteRelation()));
    }
    
    $id = $this->getPathPart(2);
    
    if (isset($_GET["comment"])) {
      if (!$this->auth(\Spit\UserType::Newbie)) {
        return;
      }
      else {
        // this effectively removes the ?comment arg, and redirects
        // the user to a url which doesn't redirect them back to the
        // login page.
        $lp = $this->app->linkProvider;
        $lp->securityRedirect = $lp->forIssue($id);
        $this->useMarkdown = true;
      }
    }
    else if ($this->auth(\Spit\UserType::Newbie, true)) {
      $this->useMarkdown = true;
    }
    
    $this->issueFields = new \Spit\IssueFields($this->app->project->name);
    
    $issue = $this->ds->getById($id, $this->app->project->id, $this->issueFields);
    
    if ($issue == null) {
      $this->showError(404);
      return;
    }
    
    $data["columns"] = $this->getDetailColumns($issue, 2);
    $data["issue"] = $issue;
    
    $cds = new \Spit\DataStores\ChangeDataStore;
    $data["changes"] = $cds->getForIssue($id);
    
    $rds = new \Spit\DataStores\RelationDataStore;
    $data["relations"] = $rds->getForIssue($id);
    
    $ads = new \Spit\DataStores\AttachmentDataStore;
    $this->attachments = $ads->getForIssue($id);
    $data["attachments"] = $this->attachments;
    
    $data["query"] = isset($_GET["query"]) ? $_GET["query"] : null;
    
    $this->showView("issues/details", $this->getIssueTitle($issue), $data, \Spit\TitleMode::Affix);
  }
  
  private function deleteRelation() {
    $rds = new \Spit\DataStores\RelationDataStore;
    $relation = $rds->getCreatorById($_GET["id"]);
    
    if (!$this->userCanDeleteRelation($relation)) {
      return null;
    }
    
    $rds->delete($_GET["id"]);
    return null;
  }
  
  public function userCanDeleteRelation($relation) {
    // only allow original creator or manager to delete relations.
    return ($this->auth(\Spit\UserType::Manager, true) ||
      ($this->app->security->isLoggedIn(true) && $relation->creatorId == $this->app->security->user->id));
  }
  
  public function canCreateRelation() {
    return $this->auth(\Spit\UserType::Member, true);
  }
  
  private function createRelation() {
    if (!$this->canCreateRelation()) {
      return null;
    }
    
    $relation = new \Spit\Models\Relation;
    
    $toId = (int)$_POST["issueId"];
    $toIssue = $this->ds->getById($toId, $this->app->project->id);
    if ($toIssue == null) {
      return array("error" => sprintf(T_("Issue does not exist: #%d"), $toId));
    }
    
    $typeSplit = explode(":", $_POST["type"]);
    $relation->type = $typeSplit[0];
    
    // the original issue we are adding the relation from.
    $fromId = $this->getPathPart(2);
    
    // make sure the user doesn't add duplicates.
    $rds = new \Spit\DataStores\RelationDataStore;
    foreach ($rds->getForIssue($fromId) as $existing) {
      if (($existing->leftId == $fromId && $existing->rightId == $toId) ||
        ($existing->rightId == $fromId && $existing->leftId == $toId)) {
        return array("error" => sprintf(
          T_("Relationship already exists between #%d and #%d."), $toId, $fromId));
      }
    }
    
    $isLeft = count($typeSplit) > 1 && $typeSplit[1] == "l";
    if ($isLeft) {
      $relation->leftId = $fromId;
      $relation->rightId = $toId;
      $relation->rightTitle = $toIssue->title;
      $relation->rightTracker = $toIssue->tracker;
      $relation->rightClosed = $toIssue->closed;
    }
    else {
      $relation->rightId = $fromId;
      $relation->leftId = $toId;
      $relation->leftTitle = $toIssue->title;
      $relation->leftTracker = $toIssue->tracker;
      $relation->leftClosed = $toIssue->closed;
    }
    
    $relation->type = (int)$_POST["type"];
    $relation->creatorId = $this->app->security->user->id;
    
    $result = array();
    
    // if duplicate, set the issue's status to duplicate
    // and add a change record for the status.
    if ($relation->type == RelationType::Duplicates) {
      $statusDataStore = new \Spit\DataStores\StatusDataStore;
      $duplicateStatus = $statusDataStore->getByName("Duplicate");
      if ($duplicateStatus != null) {
        $duplicateId = $isLeft ? $fromId : $toId;
        $oldStatus = $statusDataStore->getForIssue($duplicateId);
        $this->ds->updateStatus($duplicateId, $duplicateStatus->id, true);
        
        $issueFields = new \Spit\IssueFields($this->app->project->name);
        $changeResolver = new \Spit\ChangeResolver($issueFields);
        $this->createEditChange(
          $changeResolver, $duplicateId, "statusId",
          $oldStatus->id, $duplicateStatus->id);
        
        $result["newStatus"] = $duplicateStatus->name;
      }
    }
    
    $relation->id = $rds->insert($relation);
    
    $result["info"] = $relation->getHtmlInfo($this, $fromId);
    return $result;
  }
  
  private function getIssueTitle($issue) {
    return sprintf("%s #%d - %s", $issue->tracker, $issue->id, $issue->title);
  }
  
  private function commentPost() {
    if (!$this->app->security->isLoggedIn()) {
      return null;
    }
    
    $issueId = $this->getPathPart(2);
    
    $change = new \Spit\Models\Change;
    $change->issueId = $issueId;
    $change->creatorId = $this->app->security->user->id;
    $change->type = \Spit\Models\ChangeType::Comment;
    $change->data = $_POST["content"];
    
    $cds = new \Spit\DataStores\ChangeDataStore;
    $cds->insert($change);
    
    // values needed for "get info" functions.
    $change->created = new DateTime();
    $change->creator = $this->app->security->user->name;
    
    $this->ds->updateLastComment($issueId);
    
    return array(
      "info" => $this->getChangeInfo($change),
      "html" => $this->getChangeContent($change)
    );
  }
  
  private function insert($issue, $diff, $issueFields) {    
    $issue->projectId = $this->app->project->id;
    $issue->creatorId = $this->app->security->user->id;
    $issue->id = $this->ds->insert($issue);
    
    $this->updateCustom($issue, $diff, $issueFields);
  }
  
  private function update($issue, $diff, $issueFields) {
    $issue->closed = $this->statusIsClosed($issue->statusId);
    
    $issue->updaterId = $this->app->security->user->id;
    $this->ds->update($issue);
    
    $this->updateCustom($issue, $diff, $issueFields);
    
    $changeResolver = new \Spit\ChangeResolver($issueFields);
    foreach ($diff as $k => $v) {
      $this->createEditChange($changeResolver, $issue->id, $k, $v->oldValue, $v->newValue);
    }
  }
  
  private function createEditChange($changeResolver, $issueId, $name, $oldValue, $newValue) {
    $change = new \Spit\Models\Change;
    $change->issueId = $issueId;
    $change->creatorId = $this->app->security->user->id;
    $change->type = \Spit\Models\ChangeType::Edit;
    $change->name = $name;
    $change->oldValue = $oldValue;
    $change->newValue = $newValue;
    $changeResolver->resolve($change);
    $cds = new \Spit\DataStores\ChangeDataStore;
    $cds->insert($change);
  }
  
  private function statusIsClosed($statusId) {
    $dataStore = new \Spit\DataStores\StatusDataStore;
    $statuses = $dataStore->get();
    foreach ($statuses as $status) {
      if ($status->id == $statusId) {
        return $status->closed;
      }
    }
    return false;
  }
  
  private function updateCustom($issue, $diff, $issueFields) {
    $map = $issueFields->getCustomFieldMap();
    $fields = array();
    
    foreach ($diff as $k => $v) {
      // if the changed field is a custom field...
      if (array_key_exists($k, $map)) {
        $fields[$k] = $v->newValue;
      }
    }
    
    if (count($fields) == 0) {
      return;
    }
    
    if ($issue->customId == null) {
      $this->ds->insertCustom($issue->id, $fields);
    }
    else {
      $this->ds->updateCustom($issue->id, $fields);
    }
  }
  
  private function getTableData($page, $limit, $query = null) {
    $start = ($page - 1) * $limit;
    $results = $this->ds->get($this->app->project->id, $start, $limit, $query);
    
    $queryName = $query != null ? $query->name : null;
    
    $issues = $results[0];
    foreach ($issues as $issue) {
      $issue->link = $this->app->linkProvider->forIssue($issue->id, $queryName);
    }
    
    $this->replaceWithPublicValues($issues);
    
    return array(
      "fields" => $this->getTableFields(),
      "issues" => $issues,
      "pageCount" => ceil($results[1] / $limit),
    );
  }
  
  private function getDetailColumns($issue, $count) {
    $columns = array();
    $fields = $this->getTextFields($issue->trackerId);
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
          $field->value = $this->getPublicValue($field->mappedField, $issue);
          
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
        return sprintf("<a href=\"%s\">%s</a>", $this->app->linkProvider->forUser($id), $v);
      }
    }
    
    if ($fieldName == "created" || $fieldName == "updated" && $v != null) {
      return $this->formatDate($v);
    }
    
    if ($custom) {
      $values = $this->issueFields->getCustomFieldValues($fieldName);
      if (count($values) != 0) {
        $v = $values[$v];
      }
    }
    
    return $v;
  }
  
  private function getTextFields($trackerId) {
    
    $fields = array(
      new DisplayField("statusId", "status", T_("Status:")),
      new DisplayField("priorityId", "priority", T_("Priority:")),
      new DisplayField("assigneeId", "assignee", T_("Assignee:")),
      new DisplayField("categoryId", "category", T_("Category:")),
      new DisplayField("targetId", "target", T_("Target:")),
      new DisplayField("foundId", "found", T_("Found:")),
      new DisplayField("creatorId", "creator", T_("Created by:")),
      new DisplayField("created", "created", T_("Created on:")),
      new DisplayField("updaterId", "updater", T_("Updated by:")),
      new DisplayField("updated", "updated", T_("Updated on:"))
    );
    
    $issueFields = new \Spit\IssueFields($this->app->project->name);
    foreach ($issueFields->getCustomFieldMap() as $k => $v) {
      array_push($fields, new DisplayField($k, $k, sprintf(T_("%s:"), $v)));
    }
    
    return $issueFields->filter($fields, $trackerId);
  }
  
  private function getTableFields() {
    
    return array(
      new TableField("id", "#", true, true),
      new TableField("tracker", T_("Tracker")),
      new TableField("status", T_("Status")),
      new TableField("priority", T_("Priority")),
      new TableField("title", T_("Title"), false, true),
      new TableField("activity", T_("Activity")),
    );
  }
  
  private function getTrackerSelect($trackerId) {
    $select = new SelectField("trackerId", T_("Tracker"));
    $dataStore = new \Spit\DataStores\TrackerDataStore;
    $this->fillSelectField($select, $dataStore->get(), $trackerId);
    return $select;
  }
  
  private function fillSelectField($select, $data, $default) {
    foreach ($data as $item) {
      // only use item default if no default specified in args.
      if ($default != null) {
        $selected = $item->id == $default;
      }
      else {
        $selected = isset($item->isDefault) && $item->isDefault;
      }
      $select->add($item->id, T_($item->name), $selected);
    }
  }
  
  private function getEditorFields($trackerId, $issue) {
  
    $fields = array();
    
    $statusDataStore = new \Spit\DataStores\StatusDataStore;
    $priorityDataStore = new \Spit\DataStores\PriorityDataStore;
    $versionDataStore = new \Spit\DataStores\VersionDataStore;
    $userDataStore = new \Spit\DataStores\UserDataStore;
    $categoryDataStore = new \Spit\DataStores\CategoryDataStore;
  
    $status = new SelectField("statusId", T_("Status"));
    $this->fillSelectField($status, $statusDataStore->get(), $issue->statusId);
    array_push($fields, $status);
    
    $priority = new SelectField("priorityId", T_("Priority"));
    $this->fillSelectField($priority, $priorityDataStore->get(), $issue->priorityId);
    array_push($fields, $priority);
    
    $category = new SelectField("categoryId", T_("Category"));
    $category->add(null, "");
    $this->fillSelectField($category, $categoryDataStore->get(), $issue->categoryId);
    array_push($fields, $category);
    
    $found = new SelectField("foundId", T_("Found"));
    $found->add(null, "");
    $this->fillSelectField($found, $versionDataStore->get(), $issue->foundId);
    array_push($fields, $found);
    
    $target = new SelectField("targetId", T_("Target"));
    $target->add(null, "");
    $this->fillSelectField($target, $versionDataStore->get(), $issue->targetId);
    array_push($fields, $target);
    
    $assignee = new SelectField("assigneeId", T_("Assignee"));
    $assignee->add(null, "");
    $this->fillSelectField($assignee, $userDataStore->getMembers(), $issue->assigneeId);
    array_push($fields, $assignee);
    
    $issueFields = new \Spit\IssueFields($this->app->project->name);
    foreach ($issueFields->getCustomFieldMap() as $name => $label) {
    
      $values = $issueFields->getCustomFieldValues($name);
      
      if (count($values) != 0) {
        $custom = new SelectField($name, $label);
        $custom->add(null, "");
        
        foreach ($values as $value => $text) {
          $selected = isset($issue->$name) && $issue->$name == $value;
          $custom->add($value, $text, $selected);
        }
      }
      else {
        $custom = new TextField($name, $label);
        if (isset($issue->$name)) {
          $custom->value = $issue->$name;
        }
      }
      array_push($fields, $custom);
    }
    
    return $issueFields->filter($fields, $trackerId, true);
  }
  
  private function findAttachment($id) {
    foreach ($this->attachments as $attachment) {
      if ($attachment->id == $id) {
        return $attachment;
      }
    }
    return null;
  }
  
  public function getChangeContent($change) {
    if ($change->type == \Spit\Models\ChangeType::Comment) {
        return $this->markdown($change->data);
    }
    
    $html = "";
    
    if ($change->type == \Spit\Models\ChangeType::Upload) {
      $attachment = $this->findAttachment((int)$change->data);
      if ($attachment != null) {
        $html = $this->getAttachmentInfo($attachment); 
      }
    }
    elseif ($change->name == "details") {
      $oldLen = strlen($change->oldValue);
      $newLen = strlen($change->newValue);
      if ($oldLen > $newLen) {
        $html = sprintf(T_("Removed %d character(s)."), $oldLen - $newLen);
      }
      else {
        $html = sprintf(T_("Added %d character(s)."), $newLen - $oldLen);
      }
    }
    else {
      $format = "<span class=\"%s\">%s</span> ";
      
      if ($change->oldValue != "") {
        $html .= sprintf($format, "remove", $change->oldValue);
      }
      
      if ($change->newValue != "") {
        $html .= sprintf($format, "add", $change->newValue);
      }
    }
    
    return sprintf("<p>%s</p>", $html);
  }
  
  public function getChangeInfo($change) {
    $date = sprintf("<i>%s</i>", $this->formatDate($change->created));
    switch($change->type) {
      case ChangeType::Edit:
        return sprintf(T_("%s: %s changed %s."), $date, $change->creator, $this->getFieldLabel($change->name));
      
      case ChangeType::Comment:
        return sprintf(T_("%s: %s wrote a comment."), $date, $change->creator);
      
      case ChangeType::Upload:
        return sprintf(T_("%s: %s uploaded a file."), $date, $change->creator);
    }
    
    return null;
  }
  
  private function getFieldLabel($name) {
    switch ($name) {
      case "tracker": return T_("Tracker");
      case "title": return T_("Title");
      case "details": return T_("Details");
      case "status": return T_("Status");
      case "priority": return T_("Priority");
      case "category": return T_("Category");
      case "found": return T_("Found");
      case "target": return T_("Target");
      case "assignee": return T_("Assignee");
      default: return $name;
    }
  }
  
  public function getAttachmentInfo($attachment) {
    $link = $this->app->linkProvider->forAttachment($attachment->physicalName);
    
    $creator = "";
    if ($attachment->creator != null) {
      $creator .= $attachment->creator . " ";
    }
    $creator .= $this->formatDate($attachment->created);
    
    return sprintf("<a href=\"%s\">%s</a> - %s",
      $link, $attachment->originalName, $creator);
  }
  
  public function userCanEditAdvanced() {
    return $this->app->security->userIsType(\Spit\UserType::Member);
  }
  
  public function markdown($text) {
    foreach ($this->app->textRegex as $regex) {
      $text = preg_replace($regex->find, $regex->replace, $text);
    }
    return Markdown($text);
  }
  
  public function userCanSeeCreateLink() {
    // if newbies can create issues, always show the link so that they
    // can click new and then be redirected to login.
    if ($this->app->newIssueUserType == \Spit\UserType::Newbie) {
      return true;
    }
    
    // otherwise, if a higher level is required, only show the link if
    // they can actually use it.
    return $this->userCanCreate(true);
  }
  
  public function userCanSeequeryLink() {
    return $this->userCanEditquery(true);
  }
  
  public function userCanEditquery($passive) {
    return $this->auth(\Spit\UserType::Admin, $passive);
  }
  
  public function userCanCreate($passive = false) {
    return $this->auth($this->app->newIssueUserType, $passive);
  }
  
  public function userCanEdit($issue, $passive = false) {
    
    // allow the original author to edit their issue.
    $user = $this->app->security->isLoggedIn() ? $this->app->security->user : null;
    if ($user != null && $issue->creatorId == $user->id) {
      return true;
    }
    
    // only allow managers to edit issues raised by others.
    if ($this->auth(\Spit\UserType::Manager, $passive)) {
      return true;
    }
    
    return false;
  }
  
  private function handleQueryPost() {
    if (!$this->userCanEditquery(true)) {
      return;
    }
    
    $existing = $this->qds->getByName($_POST["name"], $this->app->project->id);
    
    $query = query::fromPost($_POST);
    $query->projectId = $this->app->project->id;
    
    if ($existing != null) {
      $this->qds->update($query);
    }
    else {
      $this->qds->insert($query);
    }
    
    header(sprintf("Location: %s/issues/%s/", $this->app->getProjectRoot(), $query->name));
    exit;
  }
  
  private function search($text) {
    $lp = $this->app->linkProvider;
    $issues = $this->ds->getBySearch($text);
    
    $results = array();
    foreach ($issues as $issue) {
      $result = new \stdClass;
      $result->id = $issue->id;
      $result->title = $issue->title;
      $result->tracker = $issue->tracker;
      $result->url = $lp->forIssue($issue->id);
      array_push($results, $result);
    }
    
    return $results;
  }
}

?>
