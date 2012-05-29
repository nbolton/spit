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

class IssueDataStore extends DataStore {

  public function get($start, $limit, $orderField, $orderDir) {
    $results = $this->multiQuery(
      "select SQL_CALC_FOUND_ROWS " .
      "i.id, i.title, i.votes, i.updated, t.name as tracker, " .
      "s.name as status, p.name as priority, u.name as assignee " .
      "from issue as i " .
      "inner join tracker as t on t.id = i.trackerId " .
      "inner join status as s on s.id = i.statusId " .
      "inner join priority as p on p.id = i.priorityId " .
      "left join user as u on u.id = i.assigneeId " .
      "order by %s %s limit %d, %d; " .
      "select FOUND_ROWS()",
      $orderField, $orderDir, $start, $limit
    );
    
    $issuesResult = $results[0];
    $totalResult = $results[1];
    
    return array(
      $this->fromResult($issuesResult),
      $this->fromResultScalar($totalResult)
    );
  }

  public function getById($id, $custom) {
    $result = $this->query(
      "select i.id, i.title, i.details, i.votes, i.created, i.updated, " .
      "i.creatorId, i.updaterId, i.revision, " .
      "t.name as tracker, s.name as status, p.name as priority, " .
      "ua.name as assignee, uu.name as updater, uc.name as creator, " .
      "vt.name as target, vf.name as found, cat.name as category " .
      $custom->getSqlString("custom.") .
      "from issue as i " .
      "inner join tracker as t on t.id = i.trackerId " .
      "inner join status as s on s.id = i.statusId " .
      "inner join priority as p on p.id = i.priorityId " .
      "left join category as cat on cat.id = i.categoryId " .
      "left join user as ua on ua.id = i.assigneeId " .
      "left join user as uu on uu.id = i.updaterId " .
      "left join user as uc on uc.id = i.creatorId " .
      "left join custom on custom.issueId = i.id " .
      "left join version as vt on vt.id = i.targetId " .
      "left join version as vf on vf.id = i.foundId " .
      "where i.id = %d",
      $id
    );
    
    return $this->fromResultSingle($result);
  }
  
  public function create($issue) {
    $this->query(
      "insert into issue " .
      "(projectId, trackerId, statusId, priorityId, " .
      "creatorId, title, details, created) " .
      "values (%d, %d, %d, %d, %d, \"%s\", \"%s\", now())",
      $issue->projectId,
      $issue->trackerId,
      $issue->statusId,
      $issue->priorityId,
      $issue->creatorId,
      $issue->title,
      $issue->details);
    
    return parent::$sql->insert_id;
  }
  
  public function update($issue) {
    $this->query(
      "update issue set " .
      "trackerId = %d, statusId = %d, priorityId = %d, updaterId = %d, " .
      "title = \"%s\", details = \"%s\", updated = now(), revision = %d " .
      "where id = %d",
      $issue->trackerId,
      $issue->statusId,
      $issue->priorityId,
      $issue->updaterId,
      $issue->title,
      $issue->details,
      $issue->revision,
      $issue->id);
  }
  
  protected function parseField($k, $v) {
    if ($k == "created" || $k == "updated") {
      return $v != "" ? new DateTime($v) : null;
    }
    else {
      return parent::parseField($k, $v);
    }
  }
  
  protected function newModel() {
    return new \Spit\Models\Issue();
  }
}

?>
