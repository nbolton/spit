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
use \Spit\Models\Fields\SelectField as SelectField;
use \Spit\Models\Fields\TextField as TextField;
use \Spit\Models\Fields\DisplayField as DisplayField;
use \Spit\EditorMode as EditorMode;
use \Spit\Models\ChangeType as ChangeType;
use \Spit\Models\RelationType as RelationType;

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
    $this->useMarkdown = true;
  
    $lp = $this->app->linkProvider;
    
    switch ($mode) {
      case EditorMode::Create: {
        $issue = new \Spit\Models\Issue;
        if (!$this->userCanCreate()) {
          return;
        }
        $lp->securityRedirect = $lp->forIssueIndex();
        break;
      }
        
      case EditorMode::Update: {
        $id = $this->getPathPart(2);
        $lp->securityRedirect = $lp->forIssue($id);
        $issue = $this->ds->getById($id, new \Spit\IssueFields($this->app));        
        if (!$this->userCanEdit($issue)) {
          return;
        }
        break;
      }
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
          $this->insert($issue, $diff);
          break;
        
        case EditorMode::Update:
          if (count($diff) != 0) {
            $this->update($issue, $diff);
          }
          break;
      }
      
      header("Location: " . $this->app->linkProvider->forIssue($issue->id));
      exit;
    }
    
    $title = ($mode == EditorMode::Create) ? T_("New Issue") : T_("Edit Issue");
    $this->showView("issues/editor", $title, $data);
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
  
  private function runDetails() {
    
    if ($this->isJsonPost()) {
      exit($this->getJson($this->commentPost()));
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
    
    $this->issueFields = new \Spit\IssueFields($this->app);
    
    $issue = $this->ds->getById($id, $this->issueFields);
    
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
    $data["attachments"] = $ads->getForIssue($id);
    
    $this->showView("issues/details", $this->getIssueTitle($issue), $data, \Spit\TitleMode::Affix);
  }
  
  private function getIssueTitle($issue) {
    return sprintf("%s #%d - %s", $issue->tracker, $issue->id, $issue->title);
  }
  
  private function commentPost() {
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
  
  private function insert($issue, $diff) {
    $issue->projectId = $this->app->project->id;
    $issue->creatorId = $this->app->security->user->id;
    $issue->id = $this->ds->insert($issue);
    
    $issueFields = new \Spit\IssueFields($this->app);
    $this->updateCustom($issue, $diff, $issueFields);
  }
  
  private function update($issue, $diff) {
    $issue->closed = $this->statusIsClosed($issue->statusId);
    
    $issue->updaterId = $this->app->security->user->id;
    $this->ds->update($issue);
    
    $issueFields = new \Spit\IssueFields($this->app);
    $this->updateCustom($issue, $diff, $issueFields);
    
    $changeResolver = new \Spit\ChangeResolver($issueFields);
    foreach ($diff as $k => $v) {
      $change = new \Spit\Models\Change;
      $change->issueId = $issue->id;
      $change->creatorId = $this->app->security->user->id;
      $change->type = \Spit\Models\ChangeType::Edit;
      $change->name = $k;
      $change->oldValue = $v->oldValue;
      $change->newValue = $v->newValue;
      $changeResolver->resolve($change);
      $cds = new \Spit\DataStores\ChangeDataStore;
      $cds->insert($change);
    }
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
  
  private function getTableData($page, $limit) {
    $start = ($page - 1) * $limit;
    $results = $this->ds->get($this->app->project->id, $start, $limit);
    
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
        return sprintf(
          "<a href=\"%susers/details/%d/\">%s</a>",
          $this->app->getProjectRoot(), $id, $v);
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
      new DisplayField("votes", "votes", T_("Votes:")),
      new DisplayField("creatorId", "creator", T_("Created by:")),
      new DisplayField("created", "created", T_("Created on:")),
      new DisplayField("updaterId", "updater", T_("Updated by:")),
      new DisplayField("updated", "updated", T_("Updated on:"))
    );
    
    $issueFields = new \Spit\IssueFields($this->app);
    foreach ($issueFields->getCustomFieldMap() as $k => $v) {
      array_push($fields, new DisplayField($k, $k, sprintf(T_("%s:"), $v)));
    }
    
    return $issueFields->filter($fields, $trackerId);
  }
  
  private function getTableFields() {
    
    return array(
      new TableField("tracker", T_("Tracker")),
      new TableField("status", T_("Status")),
      new TableField("priority", T_("Priority")),
      new TableField("title", T_("Title"), false, true),
      new TableField("assignee", T_("Assignee")),
      new TableField("activity", T_("Activity")),
      new TableField("votes", T_("Votes")),
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
      $selected = $item->id == $default || (isset($item->isDefault) && $item->isDefault);
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
    
    $issueFields = new \Spit\IssueFields($this->app);    
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
  
  public function getChangeContent($change) {
    if ($change->type == \Spit\Models\ChangeType::Comment) {
        return $this->markdown($change->data);
    }
    
    $html = "";
    
    if ($change->type == \Spit\Models\ChangeType::Upload) {
      $id = $change->data;
      $html = sprintf(T_("File: %s"), sprintf("<a href=\"?download=%d\">%d</a>", $id, $id));
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
        return sprintf(T_("%s: %s edited %s."), $date, $change->creator, $this->getFieldLabel($change->name));
      
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
  
  public function getRelationInfo($relation, $issueId) {
    if ($relation->type == RelationType::Generic) {
      $format = T_("Related to: %s");
    }
    
    if ($relation->leftId == $issueId) {
      $id = $relation->rightId;
      switch ($relation->type) {
        case RelationType::Duplicates: $format = T_("Duplicates: %s"); break;
        case RelationType::Blocks: $format = T_("Blocks: %s"); break;
        case RelationType::Follows: $format = T_("Follows: %s"); break;
      }
    }
    else {
      $id = $relation->leftId;
      switch ($relation->type) {
        case RelationType::Duplicates: $format = T_("Duplicated by: %s"); break;
        case RelationType::Blocks: $format = T_("Blocked by: %s"); break;
        case RelationType::Follows: $format = T_("Followed by: %s"); break;
      }
    }
    
    $issue = $this->ds->getTitleById($id);
    $link = $this->app->linkProvider->forIssue($issue->id);
    $issueInfo = sprintf("<a href=\"%s\">#%d</a> - %s", $link, $issue->id, $issue->title);
    return sprintf($format, $issueInfo);
  }
  
  public function getAttachmentInfo($attachment) {
    $link = $this->app->linkProvider->forAttachment($attachment);
    return sprintf("<a href=\"%s\">%s</a>", $link, $attachment->originalName);
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
}

?>
