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
use \Spit\Models\Fields\TableField as TableField;

class IssuesController extends Controller {
  
  public function __construct() {
    $this->ds = new \Spit\DataStores\IssueDataStore;
  }
  
  public function run() {
    switch ($this->getPathPart(1)) {
      case "": $this->runIndex(); break;
      case "new": $this->runNew(); break;
      default: $this->showError(404); break;
    }
  }
  
  private function runIndex() {
    $data["fields"] = $this->getFields();
    $data["issues"] = $this->ds->get(0, 15, "updated", "desc");
    $this->showView("issues/index", T_("Issues"), $data);
  }
  
  private function runNew() {
    if (isset($_GET["getFieldsFor"])) {
      exit(json_encode($this->getEditorFields($_GET["getFieldsFor"])));
    }
    
    $data = array();
    $data["saved"] = false;
    if ($this->isPost()) {
      $issue = new \Spit\Models\Issue;
      $this->setFormValues($issue);
      $this->ds->create($issue);
      $data["saved"] = true;
    }
    
    $this->showView("issues/editor", T_("New Issue"), $data);
  }
  
  private function getFields() {
    
    return array(
      new TableField("tracker", "Tracker"),
      new TableField("status", "Status"),
      new TableField("priority", "Priority"),
      new TableField("title", "Title", false),
      new TableField("assignee", "Assignee"),
      new TableField("updated", "Updated"),
      new TableField("votes", "Votes"),
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
  
  public function getValue($object, $field) {
    $value = parent::getValue($object, $field);
  
    if ($field == "title") {
      return sprintf("<a href=\"view/%d/\">%s</a>", $object->id, $value);
    }
    
    return $value;
  }
}

?>
