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

require_once "Google/Client.php";
require_once "Google/Service/Plus.php";
require_once "php/Spit/DataStores/UserDataStore.php";

use LightOpenID;

class Security {
  
  const SESSION_KEY = "userId";
  
  public $user;
  
  public function __construct($app) {
    $this->app = $app;
    $this->userDataStore = new \Spit\DataStores\UserDataStore;

    $protocol = isset($_SERVER["HTTPS"]) ? "https" : "http";
    $host = $_SERVER["SERVER_NAME"];
    $url = sprintf("%s://%s%s/login/", $protocol, $host, $app->getRoot());

    $this->google = new \Google_Client;
    $this->google->setClientId($app->settings->google->clientId);
    $this->google->setClientSecret($app->settings->google->clientSecret);
    $this->google->setRedirectUri($url);
    $this->google->setScopes(array("email", "profile"));
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
    $_SESSION["loginFrom"] = $_GET["from"];
    header("Location: " . $this->google->createAuthUrl());
  }
  
  public function finishLogin() {
    $this->google->authenticate($_GET["code"]);
    $accessToken = $this->google->getAccessToken();

    if (!empty($accessToken)) {
      $plus = new \Google_Service_Plus($this->google);
      $me = $plus->people->get("me");
      $email = $me->emails[0]->value;
      $name = $me->displayName;

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
    if (isset($_SESSION["loginFrom"])) {
      $from = urldecode($_SESSION["loginFrom"]);
    }
    else {
      $from = $this->app->getRoot() . "/";
    }
    header("Location: " . $from);
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
