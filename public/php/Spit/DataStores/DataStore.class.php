<?php

namespace Spit\DataStores;

use mysqli;

class DataStore {

  public function getSql() {
    $mysqli = new mysqli("localhost", "root", "", "spit");
    if ($mysqli->connect_errno) {
      throw new Exception("failed to connect to mysql: " . $mysqli->connect_error);
    }
    return $mysqli;
  }

}

?>