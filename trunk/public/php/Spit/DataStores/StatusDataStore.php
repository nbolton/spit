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

class StatusDataStore extends DataStore {

  const BULK_INSERT_MAX = 500;
  
  public function get() {
    $result = $this->query("select * from status order by `order`");
    return $this->fromResult($result);
  }
  
  public function getByName($name) {
    $result = $this->query("select * from status where name = %s", $name);
    return $this->fromResultSingle($result);
  }
  
  public function getForIssue($issueId) {
    $result = $this->query(
      "select s.* from status as s " .
      "inner join issue as i on i.statusId = s.id " .
      "where i.id = %d",
      (int)$issueId
    );
    return $this->fromResultSingle($result);
  }
  
  public function getImportIds() {
    $result = $this->query("select id, importId from status");
    return $this->fromResult($result);
  }
  
  public function insertMany($statuses) {
    $base = 
      "insert into status " .
      "(importId, name, closed, `order`, isDefault) values ";
    
    for ($j = 0; $j < count($statuses) / self::BULK_INSERT_MAX; $j++) {
      
      $slice = array_slice($statuses, $j * self::BULK_INSERT_MAX, self::BULK_INSERT_MAX);
      $count = count($slice);
      $values = "";
      
      for ($i = 0; $i < $count; $i++) {
        $status = $slice[$i];
        $values .= $this->format(
          "(%s, %s, %d, %d, %d)",
          self::nullInt($status->importId),
          $status->name,
          (int)$status->closed,
          (int)$status->order,
          (int)$status->isDefault)
          .($i < $count - 1 ? ", " : "");
      }
      
      $this->query($base . $values);
    }
  }
  
  public function truncate() {
    $this->query("truncate table status");
  }
}

?>
