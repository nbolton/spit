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

require_once "php/Spit/DataStores/DataStore.php";
require_once "php/Spit/Models/Query.php";

use DateTime;

class QueryDataStore extends DataStore {

  public function get($projectId) {
    $result = $this->query(
      "select name, filter, `order` from query where projectId = %d",
      (int)$projectId
    );
    
    return $this->fromResult($result);
  }

  public function getByName($name, $projectId) {
    $result = $this->query(
      "select name, filter, `order` from query where name = %s and projectId = %d",
      $name, (int)$projectId
    );
    
    return $this->fromResultSingle($result);
  }
  
  public function insert($query) {
    $this->query(
      "insert into query (name, filter, `order`, projectId) values (%s, %s, %s, %d)",
      $query->name, $query->filter, $query->order, (int)$query->projectId);
    
    return $this->sql->insert_id;
  }
  
  public function update($query) {
    $this->query(
      "update query set filter = %s, `order` = %s where name = %s",
      $query->filter, $query->order, $query->name);
  }
  
  protected function newModel() {
    return new \Spit\Models\Query();
  }
}

?>
