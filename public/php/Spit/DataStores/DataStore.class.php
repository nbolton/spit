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