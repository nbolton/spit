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

abstract class DataStore {

  public function getSql() {
    $s = \Spit\Settings::$instance;
    $mysqli = new mysqli($s->db->host, $s->db->user, $s->db->password, $s->db->database);
    if ($mysqli->connect_errno) {
      throw new Exception("failed to connect to mysql: " . $mysqli->connect_error);
    }
    return $mysqli;
  }
  
  protected function fromResult($result) {
    $data = array();
    if ($result->num_rows == 0)
      return $data;
    
    while ($row = $result->fetch_object()) {
      array_push($data, $this->fromRow($row));
    }
    
    return $data;
  }
  
  protected function fromRow($row) {
    $data = $this->newModel();
    foreach ($row as $k => $v) {
      if (is_string($v)) {
        $data->$k = mb_convert_encoding($v, "utf-8");
      }
      else {
        $data->$k = $v;
      }
    }
    return $data;
  }
  
  abstract protected function newModel();
}

?>