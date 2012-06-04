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

class LiteralString {
  public function __construct($str) {
    $this->str = $str;
  }
}

abstract class DataStore {

  protected static $globalSql;
  protected $sql;
  
  public function __construct($sql = null) {
    if ($sql != null) {
      $this->sql = $sql;
    }
    else {
      if (self::$globalSql == null) {
        self::$globalSql = $this->connect();
      }
      $this->sql = self::$globalSql;
    }
  }
  
  protected function connect() {
    $s = \Spit\Settings::$instance;
    $sql = new mysqli($s->db->host, $s->db->user, $s->db->password, $s->db->database);
    if ($sql->connect_errno) {
      throw new Exception("failed to connect to mysql: " . $sql->connect_error);
    }
    $sql->set_charset("utf8");
    return $sql;
  }
  
  public function query($format) {
    $args = $this->getSafeArgs(func_get_args());
    
    \Spit\App::$instance->queryCount++;
    
    $query = count($args) != 0 ? vsprintf($format, $args) : $format;
    $result = $this->sql->query($query);
    
    if ($result == null) {
      throw new Exception($this->sql->error);
    }
    
    return $result;
  }
  
  public function multiQuery($format) {
    $args = $this->getSafeArgs(func_get_args());
    
    \Spit\App::$instance->queryCount++;
    
    $query = count($args) != 0 ? vsprintf($format, $args) : $format;
    $this->sql->multi_query($query);
    
    $results = array();
    do {
      $result = $this->sql->store_result();
      if ($result == null) {
        throw new Exception($this->sql->error);
      }
      array_push($results, $result);
    }
    while ($this->sql->next_result());
    
    return $results;
  }
  
  private function getSafeArgs($funcArgs) {
    $args = array_slice($funcArgs, 1);
    foreach ($args as $k => $v) {
      $args[$k] = $this->cleanArg($v);
    }
    return $args;
  }
  
  private function cleanArg($v) {
    if ($v == null || $v == "") {
      return "NULL";
    }
    elseif (is_string($v)) {
      // escape any strings to prevent sql injection, and
      // also escape % for sprintf.
      return "\"" . $this->sql->escape_string(str_replace("%", "%%", $v)) . "\"";
    }
    elseif (is_object($v)) {
      return (string)$v->str;
    }
    else {
      return $v;
    }
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
    return $v;
  }
  
  protected function newModel() {
    return new \stdClass;
  }
  
  protected static function nullInt($int) {
    if ($int == null) {
      return null;
    }
    return (int)$int;
  }
  
  protected function format($format) {
    $args = $this->getSafeArgs(func_get_args());
    return vsprintf($format, $args);
  }
  
  protected function literal($str) {
    return new LiteralString($str);
  }
}

?>