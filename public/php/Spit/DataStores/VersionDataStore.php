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

require_once "php/Spit/DataStores/DataStore.php";

class VersionDataStore extends DataStore {

  const BULK_INSERT_MAX = 500;

  public function get() {
    $result = $this->query("select * from version");
    return $this->fromResult($result);
  }
  
  public function getImportIds() {
    $result = $this->query("select id, importId from version");
    return $this->fromResult($result);
  }
  
  public function insertMany($version) {
    $base = 
      "insert into version " .
      "(importId, name, releaseDate, released) values ";
    
    for ($j = 0; $j < count($version) / self::BULK_INSERT_MAX; $j++) {
      
      $slice = array_slice($version, $j * self::BULK_INSERT_MAX, self::BULK_INSERT_MAX);
      $count = count($slice);
      $values = "";
      
      for ($i = 0; $i < $count; $i++) {
        $version = $slice[$i];
        $values .= $this->format(
          "(%s, %s, %s, %d)",
          self::nullInt($version->importId),
          $version->name,
          $version->releaseDate,
          $version->released)
          .($i < $count - 1 ? ", " : "");
      }
      
      $this->query($base . $values);
    }
  }
  
  public function truncate() {
    $this->query("truncate table version");
  }
}

?>
