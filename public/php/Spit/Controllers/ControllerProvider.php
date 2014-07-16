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

namespace Spit\Controllers;

require_once "Controller.php";
require_once "IndexController.php";
require_once "IssuesController.php";
require_once "AdminController.php";
require_once "UsersController.php";
require_once "LoginController.php";
require_once "LogoutController.php";
require_once "SitemapController.php";
require_once "ChangelogController.php";

use Exception;

class ControllerProvider {

  public function __construct() {
    $this->controllers = array();
    $this->map("", new IndexController);
    $this->map("issues", new IssuesController);
    $this->map("admin", new AdminController);
    $this->map("users", new UsersController);
    $this->map("login", new LoginController);
    $this->map("logout", new LogoutController);
    $this->map("Sitemap.xml", new SitemapController);
    $this->map("changelog", new ChangelogController);
  }
  
  public function map($name, $controller) {
    $this->controllers[$name] = $controller;
  }

  public function find($name) {
    if (array_key_exists($name, $this->controllers)) {
      return $this->controllers[$name];
    }
    return null;
  }
  
  public function isSiteWide($name) {
    $controller = $this->find($name);
    if ($controller != null) {
      return $controller->siteWide;
    }
    return false;
  }
}

?>
