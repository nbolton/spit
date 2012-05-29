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

class ChangeDataStore extends DataStore {

  public function getForIssue($id) {
    $results = $this->multiQuery(
      "select SQL_CALC_FOUND_ROWS id " .
      "from `change` where id = %d " .
      "order by id asc; " .
      "select FOUND_ROWS()",
      $id
    );
    
    $changesResult = $results[0];
    $totalResult = $results[1];
    
    return array(
      $this->fromResult($changesResult),
      $this->fromResultScalar($totalResult)
    );
  }
  
  public function create($change) {
    $sql->query(
      "insert into `change` " .
      "(issueId, creatorId, type, content, created) " .
      "values (%d, %d, %d, \"%s\", now())",
      $change->issueId,
      $change->creatorId,
      $change->type,
      $change->content);
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
