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

class UserDataStore extends DataStore {

  const BULK_INSERT_MAX = 500;

  public function getMembers() {
    $result = $this->query("select * from user where typeMask & %d != 0", \Spit\UserType::Member);
    return $this->fromResult($result);
  }
  
  public function getById($id) {
    $result = $this->query("select * from user where id = %d", (int)$id);
    return $this->fromResultSingle($result);
  }
  
  public function getByEmail($email) {
    $result = $this->query("select * from user where email = %s", $email);
    return $this->fromResultSingle($result);
  }
  
  public function getByOpenId($openId) {
    $result = $this->query(
      "select u.* from identity as i " .
      "inner join user as u on u.id = i.userId " .
      "where i.url = %s",
      $openId);
    return $this->fromResultSingle($result);
  }
  
  public function getImportIds() {
    $result = $this->query("select id, importId from user");
    return $this->fromResult($result);
  }
  
  public function insert($user) {
    $this->query(
      "insert into user " .
      "(email, name, typeMask) " .
      "values (%s, %s, %d)",
      $user->email,
      $user->name,
      (int)$user->typeMask);
    
    return $this->sql->insert_id;
  }
  
  public function insertMany($users) {
    $base = 
      "insert into user " .
      "(importId, typeMask, email, name) values ";
    
    for ($j = 0; $j < count($users) / self::BULK_INSERT_MAX; $j++) {
      
      $slice = array_slice($users, $j * self::BULK_INSERT_MAX, self::BULK_INSERT_MAX);
      $count = count($slice);
      $values = "";
      
      for ($i = 0; $i < $count; $i++) {
        $issue = $slice[$i];
        $values .= $this->format(
          "(%s, %d, %s, %s)",
          self::nullInt($issue->importId),
          (int)$issue->typeMask,
          $issue->email,
          $issue->name)
          .($i < $count - 1 ? ", " : "");
      }
      
      $this->query($base . $values);
    }
  }
  
  public function truncate() {
    $this->query("truncate table user");
  }
}

?>
