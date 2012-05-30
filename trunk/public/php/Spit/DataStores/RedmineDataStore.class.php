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
    parent::__construct($sql);
  }

  public function getIssues() {
    $result = $this->query("select * from issues");
    return $this->fromResult($result);
  }
}

?>
