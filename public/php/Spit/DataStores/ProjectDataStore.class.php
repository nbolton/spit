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

namespace Spit\DataStores;

class ProjectDataStore extends DataStore {

  public function get() {
    $sql = parent::getSql();
    $result = $sql->query("select * from project");
    return $this->fromResult($result);
  }

  public function getByName($name) {
    $sql = parent::getSql();
    $result = $sql->query(sprintf(
      "select * from project where name=\"%s\"",
      $sql->escape_string($name)));
    
    if ($result->num_rows == 0) {
      return null;
    }
    
    $row = $result->fetch_object();
    return $this->fromRow($row);
  }
  
  protected function newModel() {
    return new \Spit\Models\Project();
  }
}

?>
