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
require "IssueFields.class.php";
require "EditorMode.class.php";
require "TitleMode.class.php";
require "UserType.class.php";
require "HttpCode.class.php";
require "Importer.class.php";
require "ChangeResolver.class.php";

require "Controllers/ControllerProvider.class.php";
require "Controllers/ErrorController.class.php";

require "DataStores/DataStore.class.php";
require "DataStores/IssueDataStore.class.php";
require "DataStores/ProjectDataStore.class.php";
require "DataStores/ChangeDataStore.class.php";
require "DataStores/StatusDataStore.class.php";
require "DataStores/TrackerDataStore.class.php";
require "DataStores/PriorityDataStore.class.php";
require "DataStores/VersionDataStore.class.php";
require "DataStores/UserDataStore.class.php";
require "DataStores/RedmineDataStore.class.php";
require "DataStores/AssigneeDataStore.class.php";
require "DataStores/CategoryDataStore.class.php";

require "Models/Link.class.php";
require "Models/Issue.class.php";
require "Models/Project.class.php";
require "Models/Change.class.php";
require "Models/User.class.php";
require "Models/Status.class.php";
require "Models/Priority.class.php";
require "Models/Tracker.class.php";
require "Models/Version.class.php";
require "Models/Category.class.php";

require "Models/Fields/SelectField.class.php";
require "Models/Fields/TableField.class.php";
require "Models/Fields/TextField.class.php";
require "Models/Fields/DisplayField.class.php";

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
    $this->security = new Security($this);
    $this->error = new Controllers\ErrorController($this);
    $this->path = new Path;
  }
  
  public function run() {
    $this->locale->run();
    $this->security->run();
    
    $this->initLinks();
    
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
  
  private function initLinks() {
    
    $this->links = array(
      new Link(T_("Home"), ""),
      new Link(T_("Issues"), "issues/")
    );
    
    if (!$this->security->isLoggedIn()) {
      $this->addLink(new Link(T_("Login"), "login/"));
    }
    else {
      if ($this->security->userIsType(\Spit\UserType::Admin)) {
        $this->addLink(new Link(T_("Admin"), "admin/"));
      }
    }
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
  
  // TODO: extend and move to it's own class.
  public function diff($old, $new) {
    if ($old == $new) {
      return null;
    }
    $diff = array();
    if ($old != "") array_push($diff, "-" . $old);
    if ($new != "") array_push($diff, "+" . $new);
    return implode("\n", $diff);
  }
}

?>
