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

class EditorFields {
  public $mappings;

  public function __construct($app) {
    $this->app = $app;
    $this->mappings = array();
    foreach (Settings::$instance->fields as $k => $v) {
      $this->mappings[$k] = $this->getMappings($v);
    }
  }
  
  public function getSqlString($fieldPrefix) {
    $fields = $this->getFieldMap();
    if (count($fields) == 0) {
      return "";
    }
    
    $sqlFields = array();
    foreach ($fields as $k => $v) {
      array_push($sqlFields, $fieldPrefix . $k);
    }
    
    return sprintf(", %s ", implode(", ", $sqlFields));
  }
  
  private function getCustomId() {
    if (!isset($this->mappings["projects"])) {
      return null;
    }
    
    $projectMap = $this->mappings["projects"];
    if (count($projectMap) == 0) {
      return array();
    }
    
    return $projectMap[$this->app->project->name];
  }
  
  public function getFieldMap() {
    $id = $this->getCustomId();
    if ($id == null) {
      return array();
    }
    
    return $this->mappings["fields" . $id];
  }
  
  public function filter($fields, $trackerId = null) {
    $id = $this->getCustomId();
    if ($id == null) {
      return array();
    }
    
    // if no tracker specified, we can't filter out ignored fields.
    if ($trackerId == null) {
      return $fields;
    }
  
    $excludeMap = $this->mappings["exclude" . $id];
    if (!array_key_exists($trackerId, $excludeMap)) {
      return $fields;
    }
    
    $exclude = explode(";", $excludeMap[$trackerId]);
    $result = array();
    foreach ($fields as $field) {
      // if custom field isn't excluded...
      if (!in_array($field->name, $exclude)) {
        array_push($result, $field);
      }
    }
    return $result;
  }
  
  public function getFieldValues($field) {
    $id = $this->getCustomId();
    if ($id == null) {
      return array();
    }
    
    $keyMap = $this->mappings["keys" . $id];
    
    if (!array_key_exists($field, $keyMap)) {
      return array();
    }
    
    $valueKey = $keyMap[$field];
    return $this->mappings[$valueKey];
  }
  
  private function getMappings($string) {
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
