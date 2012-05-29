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

  public function getForIssue($issueId) {
    $result = $this->query(
      "select c.id, c.creatorId, c.revision, c.type, c.name, " .
      "c.content, c.created, u.name as creator " .
      "from `change` as c " .
      "inner join user as u on u.id = c.creatorId " .
      "where c.issueId = %d " .
      "order by c.id asc",
      $issueId
    );
    
    return $this->fromResult($result);
  }
  
  public function create($change) {
    $this->query(
      "insert into `change` " .
      "(issueId, creatorId, revision, type, name, content, created) " .
      "values (%d, %d, %d, %d, \"%s\", \"%s\", now())",
      $change->issueId,
      $change->creatorId,
      $change->revision,
      $change->type,
      $change->name,
      $change->content);
    
    return parent::$sql->insert_id;
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
