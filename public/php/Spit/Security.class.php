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

namespace Spit;

use LightOpenID;

class Security {
  
  const SESSION_KEY = "userId";
  
  public $user;
  
  public function __construct($app) {
    $this->app = $app;
    $this->openId = new LightOpenID($_SERVER["HTTP_HOST"]);
    $this->userDataStore = new \Spit\DataStores\UserDataStore;
  }
  
  public function run() {
    $userId = $this->getUserId();
    if ($userId != null) {
      $this->user = $this->userDataStore->getById($userId);
    }
  }
  
  public function startLogin() {  
    $this->openId->identity = "https://www.google.com/accounts/o8/id";
    $this->openId->required = array(
      "contact/email",
      "namePerson/first",
      "namePerson/last",
    );
    header("Location: " . $this->openId->authUrl());
  }
  
  public function finishLogin() {
    if ($this->openId->validate()) {
      $attr = $this->openId->getAttributes();
      $email = $attr["contact/email"];
      $name = trim(sprintf("%s %s", $attr["namePerson/first"], $attr["namePerson/last"]));
      
      $user = $this->userDataStore->getByEmail($email);
      if ($user != null) {
        
        // update user's name if needed.
        if ($user->name != $name) {
          $user->name = $name;
          $this->userDataStore->update($user);
        }
      }
      else {
        $user = \Spit\Models\User;
        $user->email = $email;
        $user->name = $name;
        $user->id = $this->userDataStore->create($user);
      }
      
      $_SESSION[self::SESSION_KEY] = $user->id;
      header("Location: " . $this->app->getProjectRoot());
      return true;
    }
    return false;
  }
  
  public function userIsType($checkFlag) {
    return (($this->user->typeMask & $checkFlag) != 0) ? true : false;
  }
  
  private function getUserId() {
    if (isset($_SESSION[self::SESSION_KEY]) && $_SESSION[self::SESSION_KEY] != null) {
      return $_SESSION[self::SESSION_KEY];
    }
    return null;
  }
  
  public function isLoggedIn() {
    return $this->getUserId() != null;
  }
}

?>
