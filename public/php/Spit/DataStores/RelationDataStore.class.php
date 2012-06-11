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

class RelationDataStore extends DataStore {

  const BULK_INSERT_MAX = 500;
  
  public function getForIssue($issueId) {
    $result = $this->query(
      "select r.*, " .
      "li.title as leftTitle, li.closed as leftClosed, lt.name as leftTracker, " .
      "ri.title as rightTitle, ri.closed as rightClosed, rt.name as rightTracker " .
      "from relation as r " .
      "left join issue as li on li.id = r.leftId " .
      "left join issue as ri on ri.id = r.rightId " .
      "left join tracker as lt on lt.id = li.trackerId " .
      "left join tracker as rt on rt.id = ri.trackerId " .
      "where leftId = %d or rightId = %d " .
      "order by type, leftId, rightId",
      (int)$issueId, (int)$issueId);
    
    return $this->fromResult($result);
  }
  
  public function insertMany($relations) {
    $base = 
      "insert into relation " .
      "(leftId, rightId, type) values ";
    
    for ($j = 0; $j < count($relations) / self::BULK_INSERT_MAX; $j++) {
      
      $slice = array_slice($relations, $j * self::BULK_INSERT_MAX, self::BULK_INSERT_MAX);
      $count = count($slice);
      $values = "";
      
      for ($i = 0; $i < $count; $i++) {
        $relation = $slice[$i];
        $values .= $this->format(
          "(%d, %d, %d)",
          (int)$relation->leftId,
          (int)$relation->rightId,
          (int)$relation->type)
          .($i < $count - 1 ? ", " : "");
      }
      
      $this->query($base . $values);
    }
  }
  
  public function truncate() {
    $this->query("truncate table relation");
  }
  
  protected function newModel() {
    return new \Spit\Models\Relation();
  }
}

?>
