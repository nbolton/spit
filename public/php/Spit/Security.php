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

require_once "php/lightopenid/openid.php";

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
  
  public function auth($userType, $passive = false) {
    // authorize and authenticate.
    
    if (!$this->isLoggedIn()) {
      if (!$passive) {
        $this->redirectToLogin();
      }
      return false;
    }
    
    if (!$this->userIsType($userType)) {
      if (!$passive) {
        $this->app->showError(\Spit\HttpCode::Forbidden);
      }
      return false;
    }
    
    return true;
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
        $user = new \Spit\Models\User;
        $user->email = $email;
        $user->name = $name;
        $user->id = $this->userDataStore->insert($user);
      }
      
      $this->setUserId($user->id);
      $this->redirectFrom();
      return true;
    }
    return false;
  }
  
  public function setUserId($id) {
    $_SESSION[self::SESSION_KEY] = $id;
  }
  
  public function logout() {
    // php bug #19586 can stop this from working on some machines.
    unset($_SESSION[self::SESSION_KEY]);
    $this->redirectFrom();
  }
  
  private function redirectFrom() {
    header(sprintf("Location: %s", isset($_GET["from"]) ? urldecode($_GET["from"]) : ""));
  }
  
  public function redirectToLogin() {
    header(sprintf("Location: %s", $this->app->linkProvider->forLogin()));
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
    return $this->user != null;
  }
}

?>
