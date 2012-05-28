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

  public function __construct() {
    $this->mappings = new CustomMappings;
    foreach (Settings::$instance->custom as $k => $v) {
      $this->mappings->$k = $this->getMappings($v);
    }
  }
  
  public function getSqlString($fieldPrefix) {
    $fields = $this->mappings->fields;
    if (count($fields) == 0) {
      return "";
    }
    
    $sqlFields = array();
    foreach ($fields as $k => $v) {
      array_push($sqlFields, $fieldPrefix . $k);
    }
    
    return sprintf(", %s ", implode(", ", $sqlFields));
  }
  
  public function getMappings($string) {
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
  
  public function findFieldMapping($name) {
    return $this->findMapping($this->mappings->mappings, $name);
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
