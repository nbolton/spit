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
  
  public function getProjects() {
    $result = $this->query("select * from projects");
    return $this->fromResult($result);
  }

  public function getIssues() {
    $result = $this->query("select * from issues");
    return $this->fromResult($result);
  }
  
  public function getJournals() {
    $result = $this->query("select * from journals where journalized_type = 'Issue'");
    return $this->fromResult($result);
  }
  
  public function getJournalDetails() {
    $result = $this->query(
      "select jd.*, j.* " .
      "from journal_details as jd " .
      "inner join journals as j on j.id = jd.journal_id " .
      "where journalized_type = 'Issue'"
    );
    return $this->fromResult($result);
  }

  public function getStatuses() {
    $result = $this->query("select * from issue_statuses");
    return $this->fromResult($result);
  }

  public function getPriorities() {
    $result = $this->query("select id, name from enumerations where type = 'IssuePriority'");
    return $this->fromResult($result);
  }
  
  public function getUsers() {
    $result = $this->query("select * from users");
    return $this->fromResult($result);
  }
  
  public function getCustomFields() {
    $result = $this->query(
      "select id, name from custom_fields " .
      "where type = 'IssueCustomField'");
    return $this->fromResult($result);
  }
  
  public function getCustomValues() {
    $result = $this->query(
      "select customized_id, custom_field_id, value " .
      "from custom_values where customized_type = 'Issue'");
    return $this->fromResult($result);
  }
  
  public function getTrackers() {
    $result = $this->query("select * from trackers");
    return $this->fromResult($result);
  }
  
  public function getVersions() {
    $result = $this->query("select * from versions");
    return $this->fromResult($result);
  }
  
  public function getCategories() {
    $result = $this->query("select * from issue_categories");
    return $this->fromResult($result);
  }
  
  public function getRelations() {
    $result = $this->query("select * from issue_relations");
    return $this->fromResult($result);
  }
  
  public function getAttachments() {
    $result = $this->query("select * from attachments");
    return $this->fromResult($result);
  }
}

?>
