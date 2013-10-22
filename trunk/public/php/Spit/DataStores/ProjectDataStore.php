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

class ProjectDataStore extends DataStore {

  const BULK_INSERT_MAX = 500;

  public function getCount() {
    $result = $this->query(
      "select count(*) from project"
    );
    $row = $result->fetch_row();
    return $row[0];
  }

  public function getForUser($userId) {
    $result = $this->query(
      "select p.* from project as p ".
      "left join member as m on m.projectId = p.id " .
      "where p.isPublic = 1 or m.userId = %d",
      (int)$userId
    );
    return $this->fromResult($result);
  }

  public function getByName($name) {
    $result = $this->query(
      "select * from project where name=%s",
      $name);
    
    return $this->fromResultSingle($result);
  }
  
  public function getImportIds() {
    $result = $this->query("select id, importId from project");
    return $this->fromResult($result);
  }
  
  public function insertMany($projects) {
    $base = 
      "insert into project " .
      "(importId, name, title, description, isPublic) values ";
    
    for ($j = 0; $j < count($projects) / self::BULK_INSERT_MAX; $j++) {
      
      $slice = array_slice($projects, $j * self::BULK_INSERT_MAX, self::BULK_INSERT_MAX);
      $count = count($slice);
      $values = "";
      
      for ($i = 0; $i < $count; $i++) {
        $project = $slice[$i];
        $values .= $this->format(
          "(%d, %s, %s, %s, %d)",
          self::nullInt($project->importId),
          $project->name,
          $project->title,
          $project->description,
          (int)$project->isPublic)
          .($i < $count - 1 ? ", " : "");
      }
      
      $this->query($base . $values);
    }
  }
  
  public function truncate() {
    $this->query("truncate table project");
  }
  
  protected function newModel() {
    return new \Spit\Models\Project();
  }
}

?>
