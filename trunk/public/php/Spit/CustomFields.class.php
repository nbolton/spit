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

class CustomMappings { }

class CustomFields {
  public $mappings;

  public function __construct($app) {
    $this->app = $app;
    $this->mappings = new CustomMappings;
    foreach (Settings::$instance->custom as $k => $v) {
      $this->mappings->$k = $this->getMappings($v);
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
    if (!isset($this->mappings->projects)) {
      return null;
    }
    
    $projectMap = $this->mappings->projects;
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
    
    $name = "fields" . $id;
    return $this->mappings->$name;
  }
  
  public function getFieldValues($field) {
    $id = $this->getCustomId();
    if ($id == null) {
      return array();
    }
    
    $name = "keys" . $id;
    $keyMap = $this->mappings->$name;
    
    if (!array_key_exists($field, $keyMap)) {
      return array();
    }
    
    $valueKey = $keyMap[$field];
    return $this->mappings->$valueKey;
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
    $valueMap = $this->mappings->$field;
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
