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
require "Settings.class.php";
require "Locale.class.php";
require "Plugins.class.php";

require "DataStores/DataStore.class.php";
require "DataStores/IssueDataStore.class.php";

require "Models/Link.class.php";
require "Models/Issue.class.php";

class App {
  
  const DEFAULT_PAGE = "home";
  
  public function __construct() {
    $this->settings = new Settings;
    $this->locale = new Locale;
    $this->root = self::getRoot();
    $this->theme = $this->root . "theme/default";
    $this->plugins = new Plugins($this);
    $this->controllers = new Controllers\ControllerProvider;
    
    $this->links = array(
      new Link(T_("Home"), ""),
      new Link(T_("Issues"), "issues/")
    );
  }
  
  private static function getRoot() {
    $scriptName = $_SERVER['SCRIPT_NAME'];
    $pos = strrpos($scriptName, "/");
    return substr($scriptName, 0, $pos + 1);
  }
  
  public function run() {
  
    $this->plugins->load();
    
    $this->locale->run();
    
    $pathString = isset($_GET["path"]) ? $_GET["path"] : "";
    $path = preg_split('@/@', $pathString, NULL, PREG_SPLIT_NO_EMPTY);
  
    $controller = $this->controllers->get($path);
    $controller->app = $this;
    $controller->run($path);
  }
  
  public function addLink($link) {
    array_push($this->links, $link);
  }
  
  public function addController($name, $controller) {
    $this->controllers->map($name, $controller);
  }
}

?>
