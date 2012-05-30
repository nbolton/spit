<?php

/*
 * SPIT: Simple PHP data Tracker
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

namespace Spit\DataStores;

use mysqli;
use Exception;

abstract class DataStore {

  protected static $sql;
  
  public function __construct() {
    if (self::$sql == null) {
      self::$sql = self::connect();
    }
  }
  
  private static function connect() {
    $s = \Spit\Settings::$instance;
    $sql = new mysqli($s->db->host, $s->db->user, $s->db->password, $s->db->database);
    if ($sql->connect_errno) {
      throw new Exception("failed to connect to mysql: " . $sql->connect_error);
    }
    return $sql;
  }
  
  public function query($format) {
    $args = $this->getSafeArgs(func_get_args());
    
    \Spit\App::$instance->queryCount++;
    $result = self::$sql->query(vsprintf($format, $args));
    
    if ($result == null) {
      throw new Exception(self::$sql->error);
    }
    
    return $result;
  }
  
  public function multiQuery($format) {
    $args = $this->getSafeArgs(func_get_args());
    
    \Spit\App::$instance->queryCount++;
    self::$sql->multi_query(vsprintf($format, $args));
    
    $results = array();
    do {
      $result = self::$sql->store_result();
      if ($result == null) {
        throw new Exception(self::$sql->error);
      }
      array_push($results, $result);
    }
    while (self::$sql->next_result());
    
    return $results;
  }
  
  private function getSafeArgs($funcArgs) {
    $args = array_slice($funcArgs, 1);
    
    // escape any strings to prevent sql injection.
    foreach ($args as $k => $v) {
      $args[$k] = self::$sql->escape_string($v);
    }
    
    return $args;
  }
  
  protected function fromResult($result) {
    $data = array();
    if ($result == null || $result->num_rows == 0) {
      return $data;
    }
    
    while ($row = $result->fetch_object()) {
      array_push($data, $this->fromRow($row));
    }
    
    return $data;
  }
  
  protected function fromResultSingle($result) {
    if ($result == null || $result->num_rows == 0) {
      return null;
    }
    
    return $this->fromRow($result->fetch_object());
  }
  
  protected function fromRow($row) {
    $data = $this->newModel();
    foreach ($row as $k => $v) {
      $data->$k = $this->parseField($k, $v);
    }
    return $data;
  }
  
  protected function fromResultScalar($result) {
    if ($result == null || $result->num_rows == 0) {
      return null;
    }
    $row = $result->fetch_row();
    return $row[0];
  }
  
  protected function parseField($k, $v) {
    if (is_string($v)) {
      return mb_convert_encoding($v, "utf-8");
    }
    return $v;
  }
  
  protected function newModel() {
    return new \stdClass;
  }
}

?>