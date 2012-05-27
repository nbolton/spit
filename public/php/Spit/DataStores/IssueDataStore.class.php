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
    $result = $this->query(
      "select i.*, t.name as tracker, s.name as status, " .
      "p.name as priority, u.name as assignee " .
      "from issue as i " .
      "inner join tracker as t on t.id = i.trackerId " .
      "inner join status as s on s.id = i.statusId " .
      "inner join priority as p on p.id = i.priorityId " .
      "left join user as u on u.id = i.assigneeId " .
      "order by updated desc " .
      "limit 0, 100"
    );
    return $this->fromResult($result);
  }
  
  public function create($issue) {
    $sql = parent::getSql();
    $sql->query(sprintf(
      "insert into issue (title, details) values (\"%s\", \"%s\")",
      $sql->escape_string($issue->title),
      $sql->escape_string($issue->details)));
  }
  
  protected function newModel() {
    return new \Spit\Models\Issue();
  }
}

?>
