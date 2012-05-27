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

session_start();

require "Controllers/ControllerProvider.class.php";
require "Controllers/ErrorController.class.php";
require "Settings.class.php";
require "Locale.class.php";
require "Plugins.class.php";
require "Security.class.php";
require "Path.class.php";

require "DataStores/DataStore.class.php";
require "DataStores/IssueDataStore.class.php";

require "Models/Link.class.php";
require "Models/Issue.class.php";
require "Models/Fields/Select.class.php";

class App {
  
  const DEFAULT_PAGE = "home";
  
  public function __construct() {
    $this->settings = new Settings;
    $this->locale = new Locale;
    $this->root = self::getRoot();
    $this->theme = $this->root . "theme/default";
    $this->plugins = new Plugins($this);
    $this->controllers = new Controllers\ControllerProvider;
    $this->security = new Security;
    $this->error = new Controllers\ErrorController($this);
    $this->path = new Path;
    
    $this->links = array(
      new Link(T_("Home"), ""),
      new Link(T_("Issues"), "issues/")
    );
    
    if ($this->security->userIsType("admin")) {
      $this->addLink(new Link(T_("Admin"), "admin/"));
    }
  }
  
  private static function getRoot() {
    $scriptName = $_SERVER['SCRIPT_NAME'];
    $pos = strrpos($scriptName, "/");
    return substr($scriptName, 0, $pos + 1);
  }
  
  public function run() {
  
    $this->locale->run();
    $this->plugins->load();
    
    $this->controller = $this->controllers->find($this->path);
    if ($this->controller == null) {
      $this->controller = $this->error;
      $this->error->show(404);
      return;
    }
    
    $this->controller->app = $this;
    $this->controller->run();
  }
  
  public function addLink($link) {
    array_push($this->links, $link);
  }
  
  public function addController($name, $controller) {
    $this->controllers->map($name, $controller);
  }
  
  public function getFullLink($link) {
    if ($link->external) {
      return $link->href;
    }
    return $this->root . $link->href;
  }
}

?>
