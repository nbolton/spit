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

use DateTime;

class ChangeDataStore extends DataStore {

  const BULK_INSERT_MAX = 500;
  
  public function getForIssue($issueId) {
    $result = $this->query(
      "select c.id, c.creatorId, c.type, c.name, " .
      "c.content, c.created, u.name as creator " .
      "from `change` as c " .
      "left join user as u on u.id = c.creatorId " .
      "where c.issueId = %d " .
      "order by c.id asc",
      $issueId
    );
    
    return $this->fromResult($result);
  }
  
  public function insert($change) {
    $this->query(
      "insert into `change` " .
      "(issueId, creatorId, type, name, content, created) " .
      "values (%d, %d, %d, \"%s\", \"%s\", now())",
      $change->issueId,
      $change->creatorId,
      $change->type,
      $change->name,
      $change->content);
    
    return $this->sql->insert_id;
  }
  
  public function insertMany($issues) {
    $base = 
      "insert into `change` " .
      "(issueId, creatorId, type, name, content, created) values ";
    
    for ($j = 0; $j < count($issues) / self::BULK_INSERT_MAX; $j++) {
      
      $slice = array_slice($issues, $j * self::BULK_INSERT_MAX, self::BULK_INSERT_MAX);
      $count = count($slice);
      $values = "";
      
      for ($i = 0; $i < $count; $i++) {
        $change = $slice[$i];
        $values .= sprintf(
          "(%d, %d, %d, \"%s\", \"%s\", \"%s\")%s",
          $change->issueId,
          $change->creatorId,
          $change->type,
          $change->name,
          $this->escape($change->content),
          $change->created,
          $i < $count - 1 ? ", " : "");
      }
      
      $this->query($base . $values);
    }
  }
  
  public function truncate() {
    $this->query("truncate table `change`");
  }
  
  protected function parseField($k, $v) {
    if ($k == "created") {
      return $v != "" ? new DateTime($v) : null;
    }
    else {
      return parent::parseField($k, $v);
    }
  }
  
  protected function newModel() {
    return new \Spit\Models\Change();
  }
}

?>
