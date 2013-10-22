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

class IssueFields {
  public $mappings;

  public function __construct($projectName) {
    
    $this->mappings = array();
    if (isset(Settings::$instance->fields)) {
      foreach (Settings::$instance->fields as $k => $v) {
        $this->mappings[$k] = $this->getCustomMappings($v);
      }
    }
    
    $this->customId = $this->getCustomId($projectName);
  }
  
  private function getCustomId($projectName) {
    if (!isset($this->mappings["projects"])) {
      return null;
    }
    
    $projectMap = $this->mappings["projects"];
    if (count($projectMap) == 0) {
      return null;
    }
    
    if (array_key_exists($projectName, $projectMap)) {
      return $projectMap[$projectName];
    }
    
    return null;
  }
  
  public function getCustomFieldMap() {
    if ($this->customId == null) {
      return array();
    }
    
    $name = "fields" . $this->customId;
    if (isset($this->mappings[$name])) {
      return $this->mappings[$name];
    }
    return null;
  }
  
  public function validate($fields, $trackerId) {
    // TODO: class file - ValidateResult
    $r = new \stdClass;
    $r->invalid = array();
    
    if ($this->customId == null) {
      return $r;
    }
    
    $requiredMap = $this->mappings["required" . $this->customId];
    $required = array();
    
    if (array_key_exists("*", $requiredMap)) {
      $required = array_merge($required, explode(";", $requiredMap["*"]));
    }
    
    if (array_key_exists($trackerId, $requiredMap)) {
      $required = array_merge($required, explode(";", $requiredMap[$trackerId]));
    }
    
    foreach ($fields as $k => $v) {
      if (in_array($k, $required) && $v == null) {
        $r->invalid[$k] = T_("Required field.");
      }
    }
    
    return $r;
  }
  
  public function filter($fields, $trackerId = null, $forEditor = false) {
    // do not filter if there are no settings for this project.
    // if no tracker specified, we can't filter out ignored fields.
    if ($this->customId == null || $trackerId == null) {
      return $fields;
    }
  
    $excludeMap = $this->mappings["exclude" . $this->customId];
    $readOnlyMap = $this->mappings["readOnly" . $this->customId];
    
    if (array_key_exists("*", $readOnlyMap)) {
      $readOnly = explode(";", $readOnlyMap["*"]);
    }
    else if (array_key_exists($trackerId, $readOnlyMap)) {
      $readOnly = explode(";", $readOnlyMap[$trackerId]);
    }
    else {
      $readOnly = array();
    }
    
    if (array_key_exists($trackerId, $excludeMap)) {
      $exclude = explode(";", $excludeMap[$trackerId]);
    }
    else {
      $exclude = array();
    }
    
    if (count($readOnly) == 0 && count($exclude) == 0) {
      return $fields;
    }
    
    $result = array();
    foreach ($fields as $field) {
      // if custom field isn't excluded...
      if (!in_array($field->name, $exclude) &&
        (!$forEditor || !in_array($field->name, $readOnly))) {
        array_push($result, $field);
      }
    }
    return $result;
  }
  
  public function getCustomFieldValues($field) {
    if ($this->customId == null) {
      return array();
    }
    
    $keyMap = $this->mappings["keys" . $this->customId];
    
    if (!array_key_exists($field, $keyMap)) {
      return array();
    }
    
    $valueKey = $keyMap[$field];
    return $this->mappings[$valueKey];
  }
  
  private function getCustomMappings($string) {
    $mappingStrings = explode(",", $string);
    $mappings = array();
    foreach ($mappingStrings as $mappingString) {
      $parts = explode("=", $mappingString);
      $mappings[trim($parts[0])] = trim($parts[1]);
    }
    return $mappings;
  }
  
  public function mapValue($field, $key) {
    $valueMap = $this->mappings[$field];
    return $valueMap[$key];
  }
  
  public function findMapping($mappings, $name) {
    foreach ($mappings as $a => $b) {
      if ($a == $name) {
        return $b;
      }
    }
    return null;
  }
}

?>
