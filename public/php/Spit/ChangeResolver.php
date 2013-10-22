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

require_once "php/Spit/DataStores/AssigneeDataStore.php";

class ChangeResolver {
  public $issueFields;

  public function __construct($issueFields) {
    $this->issueFields = $issueFields;
    $this->customFields = $issueFields->getCustomFieldMap();
    $this->initStaticFields();
  }
  
  private function initStaticFields() {
    $this->staticFields = array();
    $this->addField("trackerId", "tracker", new \Spit\DataStores\TrackerDataStore);
    $this->addField("statusId", "status", new \Spit\DataStores\StatusDataStore);
    $this->addField("priorityId", "priority", new \Spit\DataStores\PriorityDataStore);
    $this->addField("foundId", "found", new \Spit\DataStores\VersionDataStore);
    $this->addField("targetId", "target", new \Spit\DataStores\VersionDataStore);
    $this->addField("assigneeId", "assignee", new \Spit\DataStores\AssigneeDataStore);
    $this->addField("categoryId", "category", new \Spit\DataStores\CategoryDataStore);
  }
  
  private function addField($id, $name, $dataStore = null) {
    $field = new \stdClass;
    $field->name = $name;
    $field->dataStore = $dataStore;
    $this->staticFields[$id] = $field;
  }
  
  private function getDataEnumMap($values) {
    $map = array();
    foreach ($values as $value) {
      $map[$value->id] = $value->name;
    }
    return $map;
  }
  
  public function resolve($change) {
    if (array_key_exists($change->name, $this->staticFields)) {
      $field = $this->staticFields[$change->name];
      $name = $field->name;
      
      if (isset($field->dataStore)) {
        $map = $this->getDataEnumMap($field->dataStore->get());
      }
    }
    
    if (array_key_exists($change->name, $this->customFields)) {
      $name = $this->customFields[$change->name];
      $map = $this->issueFields->getCustomFieldValues($change->name);
    }
    
    if (isset($name)) {
      $change->name = $name;
    }
    
    if (isset($map)) {
      if (array_key_exists($change->oldValue, $map)) {
        $change->oldValue = $map[$change->oldValue];
      }
      if (array_key_exists($change->newValue, $map)) {
        $change->newValue = $map[$change->newValue];
      }
    }
  }
}

?>
