<?php

namespace Spit\DataStores;

use mysqli;

class DataStore {

  public function getSql() {
    $s = \Spit\Settings::$instance;
    $mysqli = new mysqli($s->db->host, $s->db->user, $s->db->password, $s->db->database);
    if ($mysqli->connect_errno) {
      throw new Exception("failed to connect to mysql: " . $mysqli->connect_error);
    }
    return $mysqli;
  }

}

?>