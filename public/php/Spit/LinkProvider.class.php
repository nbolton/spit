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

class LinkProvider {
  public $app;
  public $securityRedirect;

  public function __construct($app) {
    $this->app = $app;
    $this->exclude = array();
  }
  
  public function forIssue($id) {
    return sprintf("%s/issues/details/%d/", $this->app->getProjectRoot(), $id);
  }
  
  public function forIssueIndex() {
    return sprintf("%s/issues/", $this->app->getProjectRoot());
  }
  
  public function forAttachment($attachment) {
    return sprintf("%s/files/%s", $this->getRoot(), $attachment->physicalName);
  }
  
  public function forLogin($prefixRoot = true) {
    if ($prefixRoot) {
      return sprintf("%s/login/?from=%s", $this->app->getRoot(), $this->getFrom());
    }
    else {
      return sprintf("login/?from=%s", $this->getFrom());
    }
  }
  
  public function forLogout() {
    return sprintf("%s/logout/?from=%s", $this->app->getRoot(), $this->getFrom());
  }
  
  public function forUser($id) {
    return sprintf("%s/users/details/%d/", $this->app->getRoot(), $id);
  }
  
  public function forIssueEdit($id) {
    return sprintf("%s/issues/edit/%d/", $this->app->getProjectRoot(), $id);
  }
  
  public function excludeArg($arg) {
    array_push($this->exclude, $arg);
  }
  
  private function getFrom() {
    if ($this->securityRedirect != null) {
      $from = $this->securityRedirect;
    }
    else {
      $from = $_SERVER["REQUEST_URI"];
    }
    
    // remove excluded args
    foreach ($this->exclude as $arg) {
      $from = preg_replace("/$arg(=[^&])*&*/", "", $from);
    }
    
    // remove trailing ? or &
    $from = preg_replace("/(.*)[&?]$/", "$1", $from);
    
    return urlencode($from);
  }
}

?>
