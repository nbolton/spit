<?php

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
