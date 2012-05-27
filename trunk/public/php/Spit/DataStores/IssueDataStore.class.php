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

class IssueDataStore extends DataStore {

  public function get() {
    $sql = parent::getSql();
    $result = $sql->query("select * from issue");
    return $this->fromResult($result);
  }
  
  public function create($issue) {
    $sql = parent::getSql();
    $sql->query(sprintf(
      "insert into issue (title, details) values (\"%s\", \"%s\")",
      $sql->escape_string($issue->title),
      $sql->escape_string($issue->details)));
  }
  
  private function fromResult($result) {
    $issues = array();
    if ($result->num_rows == 0)
      return $issues;
    
    while ($row = $result->fetch_object()) {
      array_push($issues, $this->fromRow($row));
    }
    
    return $issues;
  }
  
  private function fromRow($row) {
    $issue = new \Spit\Models\Issue();
    $issue->id = $row->id;
    $issue->title = mb_convert_encoding($row->title, "utf-8");
    $issue->details = mb_convert_encoding($row->details, "utf-8");
    return $issue;
  }
}

?>