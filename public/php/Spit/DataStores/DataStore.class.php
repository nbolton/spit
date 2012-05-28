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

  public function getSql() {
    $s = \Spit\Settings::$instance;
    $mysqli = new mysqli($s->db->host, $s->db->user, $s->db->password, $s->db->database);
    if ($mysqli->connect_errno) {
      throw new Exception("failed to connect to mysql: " . $mysqli->connect_error);
    }
    return $mysqli;
  }
  
  public function query($format) {
    $sql = $this->getSql();
    $args = $this->getSafeArgs(func_get_args(), $sql);
    
    $result = $sql->query(vsprintf($format, $args));
    
    if ($result == null) {
      throw new Exception($sql->error);
    }
    
    return $result;
  }
  
  public function multiQuery($format) {
    $sql = $this->getSql();
    $args = $this->getSafeArgs(func_get_args(), $sql);
    
    $sql->multi_query(vsprintf($format, $args));
    
    $results = array();
    do {
      $result = $sql->store_result();
      if ($result == null) {
        throw new Exception($sql->error);
      }
      array_push($results, $result);
    }
    while ($sql->next_result());
    
    return $results;
  }
  
  private function getSafeArgs($funcArgs, $sql) {
    $args = array_slice($funcArgs, 1);
    
    // escape any strings to prevent sql injection.
    foreach ($args as $k => $v) {
      $args[$k] = $sql->escape_string($v);
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
  
  abstract protected function newModel();
}

?>