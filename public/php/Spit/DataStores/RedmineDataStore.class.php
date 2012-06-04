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

use mysqli;

class RedmineDataStore extends DataStore {

  public function __construct($host, $user, $pass, $name) {
    
    $sql = new mysqli($host, $user, $pass, $name);
    if ($sql->connect_errno) {
      throw new Exception("failed to connect to mysql: " . $sql->connect_error);
    }
    $sql->set_charset("utf8");
    parent::__construct($sql);
  }

  public function getIssues() {
    $result = $this->query("select * from issues");
    return $this->fromResult($result);
  }
  
  public function getJournalDetails() {
    $result = $this->query(
      "select j.*, jd.* from journals as j " .
      "left join journal_details as jd on jd.journal_id = j.id " .
      "where j.journalized_type = \"Issue\""
    );
    return $this->fromResult($result);
  }

  public function getStatuses() {
    $result = $this->query("select * from issue_statuses");
    return $this->fromResult($result);
  }

  public function getPriorities() {
    $result = $this->query("select id, name from enumerations where type = \"IssuePriority\"");
    return $this->fromResult($result);
  }
  
  public function getUsers() {
    $result = $this->query("select * from users");
    return $this->fromResult($result);
  }
  
  public function getVotes() {
    $exists = $this->query("show tables like 'votes'")->num_rows == 1;
    if (!$exists) {
      return array();
    }
    
    $result = $this->query(
      "select i.id, " .
      "(select count(*) from votes where voteable_id = i.id) as votes " .
      "from issues as i");
    
    return $this->fromResult($result);
  }
}

?>
