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

  const BULK_INSERT_MAX = 500;
  
  public function get($start, $limit, $orderField, $orderDir) {
    $results = $this->multiQuery(
      "select SQL_CALC_FOUND_ROWS " .
      "i.id, i.assigneeId, i.title, i.votes, i.updated, t.name as tracker, " .
      "s.name as status, p.name as priority, u.name as assignee " .
      "from issue as i " .
      "inner join tracker as t on t.id = i.trackerId " .
      "inner join status as s on s.id = i.statusId " .
      "inner join priority as p on p.id = i.priorityId " .
      "left join user as u on u.id = i.assigneeId " .
      "order by %s %s limit %d, %d; " .
      "select FOUND_ROWS()",
      $this->literal($orderField),
      $this->literal($orderDir),
      (int)$start, (int)$limit
    );
    
    $issuesResult = $results[0];
    $totalResult = $results[1];
    
    return array(
      $this->fromResult($issuesResult),
      $this->fromResultScalar($totalResult)
    );
  }
  
  public function getImportIds() {
    $result = $this->query("select id, importId from issue");
    return $this->fromResult($result);
  }

  public function getById($id, $custom) {
    $result = $this->query(
      "select i.id, i.trackerId, i.statusId, i.priorityId, i.categoryId, " .
      "i.targetId, i.foundId, i.assigneeId, i.creatorId, i.updaterId, " .
      "i.title, i.details, i.votes, i.created, i.updated, " .
      "t.name as tracker, s.name as status, p.name as priority, " .
      "ua.name as assignee, uu.name as updater, uc.name as creator, " .
      "vt.name as target, vf.name as found, cat.name as category, " .
      "custom.id as customId " .
      $this->getCustomSelect($custom, "custom.") .
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
      (int)$id
    );
    
    return $this->fromResultSingle($result);
  }
  
  public function getTitleById($id) {
    $result = $this->query(
      "select id, title from issue where id = %d",
      (int)$id
    );
    
    return $this->fromResultSingle($result);
  }
  
  public function insert($issue) {
    $this->query(
      "insert into issue " .
      "(projectId, trackerId, statusId, priorityId, categoryId, targetId, " .
      "foundId, assigneeId, creatorId, title, details, created) " .
      "values (%d, %d, %d, %d, %s, %s, %s, %s, %s, %s, %s, now())",
      (int)$issue->projectId,
      (int)$issue->trackerId,
      (int)$issue->statusId,
      (int)$issue->priorityId,
      self::nullInt($issue->categoryId),
      self::nullInt($issue->targetId),
      self::nullInt($issue->foundId),
      self::nullInt($issue->assigneeId),
      self::nullInt($issue->creatorId),
      $issue->title,
      $issue->details);
    
    return $this->sql->insert_id;
  }
  
  public function insertMany($issues) {
    $base = 
      "insert into issue " .
      "(projectId, trackerId, statusId, priorityId, categoryId, targetId, " .
      "foundId, assigneeId, creatorId, updaterId, importId, " .
      "title, details, votes, created, updated) values ";
    
    for ($j = 0; $j < count($issues) / self::BULK_INSERT_MAX; $j++) {
      
      $slice = array_slice($issues, $j * self::BULK_INSERT_MAX, self::BULK_INSERT_MAX);
      $count = count($slice);
      $values = "";
      
      for ($i = 0; $i < $count; $i++) {
        $issue = $slice[$i];
        $values .= $this->format(
          "(%d, %d, %d, %d, %s, %s, %s, %s, %s, %s, %s, %s, %s, %d, %s, %s)",
          (int)$issue->projectId,
          (int)$issue->trackerId,
          (int)$issue->statusId,
          (int)$issue->priorityId,
          self::nullInt($issue->categoryId),
          self::nullInt($issue->targetId),
          self::nullInt($issue->foundId),
          self::nullInt($issue->assigneeId),
          self::nullInt($issue->creatorId),
          self::nullInt($issue->updaterId),
          self::nullInt($issue->importId),
          $issue->title,
          $issue->details,
          (int)$issue->votes,
          $issue->created,
          $issue->updated)
          .($i < $count - 1 ? ", " : "");
      }
      
      $this->query($base . $values);
    }
  }
  
  public function update($issue) {
    $this->query(
      "update issue set " .
      "trackerId = %d, statusId = %d, priorityId = %d, categoryId = %s, targetId = %s, " .
      "foundId = %s, assigneeId = %s, updaterId = %s, " .
      "title = %s, details = %s, updated = now() " .
      "where id = %d",
      (int)$issue->trackerId,
      (int)$issue->statusId,
      (int)$issue->priorityId,
      self::nullInt($issue->categoryId),
      self::nullInt($issue->targetId),
      self::nullInt($issue->foundId),
      self::nullInt($issue->assigneeId),
      self::nullInt($issue->updaterId),
      $issue->title,
      $issue->details,
      (int)$issue->id);
  }
  
  // TODO: move to new data store
  public function updateCustom($issueId, $map) {
    
    $pairs = array();
    foreach ($map as $k => $v) {
      array_push($pairs, $this->format("%s = %s", $this->literal($k), $v));
    }
    
    $this->query(
      "update custom set %s where issueId = %d",
      $this->literal(implode(", ", $pairs)),
      (int)$issueId
    );
  }
  
  // TODO: move to new data store
  public function insertCustom($issueId, $map) {
    
    $fields = array();
    $values = array();
    foreach ($map as $k => $v) {
      array_push($fields, $k);
      array_push($values, $this->cleanArg($v));
    }
    
    $this->query(
      "insert into custom (issueId, %s) values (%d, %s)",
      $this->literal(implode(", ", $fields)),
      (int)$issueId,
      $this->literal(implode(", ", $values))
    );
  }
  
  // TODO: move to new data store
  public function insertCustomMany($fields, $valueLists) {
    $base = sprintf(
      "insert into custom (issueId, %s) values ",
      implode(", ", $fields));
    
    for ($j = 0; $j < count($valueLists) / self::BULK_INSERT_MAX; $j++) {
      
      $slice = array_slice($valueLists, $j * self::BULK_INSERT_MAX, self::BULK_INSERT_MAX);
      $count = count($slice);
      $sql = "";
      
      for ($i = 0; $i < $count; $i++) {
        $custom = $slice[$i];
        
        $cleanValues = array();
        foreach ($custom->values as $value) {
          array_push($cleanValues, $this->cleanArg($value));
        }
        
        $sql .= $this->format(
          "(%d, %s)",
          (int)$custom->id,
          $this->literal(implode(", ", $cleanValues)))
          .($i < $count - 1 ? ", " : "");
      }
      
      $this->query($base . $sql);
    }
  }
  
  public function truncate() {
    $this->query("truncate table issue");
    $this->query("truncate table `custom`");
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
  
  private function getCustomSelect($custom, $fieldPrefix) {
    $fields = $custom->getCustomFieldMap();
    if (count($fields) == 0) {
      return "";
    }
    
    $sqlFields = array();
    foreach ($fields as $k => $v) {
      array_push($sqlFields, $fieldPrefix . $k);
    }
    
    return sprintf(", %s ", implode(", ", $sqlFields));
  }
}

?>
