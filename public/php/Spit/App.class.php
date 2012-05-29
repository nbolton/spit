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

require "Settings.class.php";
require "Locale.class.php";
require "Plugins.class.php";
require "Security.class.php";
require "Path.class.php";
require "CustomFields.class.php";
require "EditorMode.class.php";

require "Controllers/ControllerProvider.class.php";
require "Controllers/ErrorController.class.php";

require "DataStores/DataStore.class.php";
require "DataStores/IssueDataStore.class.php";
require "DataStores/ProjectDataStore.class.php";
require "DataStores/ChangeDataStore.class.php";

require "Models/Link.class.php";
require "Models/Issue.class.php";
require "Models/Project.class.php";
require "Models/Change.class.php";
require "Models/User.class.php";

require "Models/Fields/Select.class.php";
require "Models/Fields/TableField.class.php";

class App {
  
  const DEFAULT_PAGE = "home";
  
  public static $instance;
  
  public $queryCount = 0;
  
  public function __construct() {
    self::$instance = $this;
    $this->start = microtime(true);
    $this->settings = new Settings;
    $this->locale = new Locale;
    $this->plugins = new Plugins($this);
    $this->controllers = new Controllers\ControllerProvider;
    $this->security = new Security;
    $this->error = new Controllers\ErrorController($this);
    $this->path = new Path;
    
    // TODO: take values from database.
    $this->user = new Models\User;
    $this->user->id = 1;
    $this->user->name = "Nick Bolton";
    
    $this->links = array(
      new Link(T_("Home"), ""),
      new Link(T_("Issues"), "issues/")
    );
    
    if ($this->security->userIsType("admin")) {
      $this->addLink(new Link(T_("Admin"), "admin/"));
    }
  }
  
  public function run() {
    
    $this->locale->run();
    
    $this->project = $this->findProject();
    if ($this->project == null) {
      $this->showError(404);
      return;
    }
    
    if (!$this->isSingleProject()) {
      $this->setupMultiProject();
    }
    
    $this->plugins->load();
    
    $this->controller = $this->controllers->find($this->path->get(0));
    if ($this->controller == null) {
      $this->showError(404);
      return;
    }
    
    $this->controller->app = $this;
    $this->controller->run();
  }
  
  private function setupMultiProject() {
  
    $this->addLink(new Link(T_("Projects"), $this->getRoot(), true));
    
    // offset the path so that pages wanting index 1 get 2,
    // since the part at 0 is now the project name.
    $this->path->setOffset(1);
  }
  
  private function findProject() {
    $dataStore = new DataStores\ProjectDataStore;
    if ($this->isSingleProject()) {
      return $dataStore->getByName($this->settings->site->singleProject);
    }
    else {
      return $dataStore->getByName($this->path->get(0));
    }
  }
  
  public function showError($code) {
    $this->controller = $this->error;
    $this->error->show($code);
  }
  
  public function getRoot() {
    $scriptName = $_SERVER['SCRIPT_NAME'];
    $pos = strrpos($scriptName, "/");
    return substr($scriptName, 0, $pos + 1);
  }
  
  public function getProjectRoot() {
    $root = $this->getRoot();
    if ($this->isSingleProject() || !isset($this->project)) {
      return $root;
    }
    return $root . $this->project->name . "/";
  }
  
  public function getThemeRoot() {
    return $this->getRoot() . "theme/default";
  }
  
  public function isSingleProject() {
    return isset($this->settings->site->singleProject);
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
    return $this->getProjectRoot() . $link->href;
  }
  
  public function getImagePath($name) {
    return sprintf("%s/image/%s", $this->getThemeRoot(), $name);
  }
  
  public function getSiteTitle() {
    if ($this->project != null) {
      return $this->project->title;
    }
    else {
      return $this->settings->site->defaultTitle;
    }
  }
  
  public function getSiteDescription() {
    if ($this->project != null) {
      return $this->project->description;
    }
    else {
      return $this->settings->site->defaultDescription;
    }
  }
  
  public function getLoadTime() {
    // microtime result appears to be in seconds... odd.
    return (microtime(true) - $this->start) * 1000;
  }
}

?>
