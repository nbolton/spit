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
require "LinkProvider.class.php";

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
require "DataStores/RelationDataStore.class.php";
require "DataStores/AttachmentDataStore.class.php";
require "DataStores/MemberDataStore.class.php";

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
require "Models/Relation.class.php";

require "Models/Fields/SelectField.class.php";
require "Models/Fields/TableField.class.php";
require "Models/Fields/TextField.class.php";
require "Models/Fields/DisplayField.class.php";

class App {
  
  const DEFAULT_PAGE = "home";
  
  public static $instance;
  
  public $queryCount = 0;
  public $project;
  
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
    $this->linkProvider = new LinkProvider($this);
    
    // links that can be accessed even if there is no project.
    $this->globalLinks = array(null, "login", "logout", "admin", "Sitemap.xml");
    
    // default user level needed to create new issues.
    $this->newIssueUserType = UserType::Newbie;
    
    $this->links = array();
    $this->textRegex = array();
  }
  
  public function run() {
    $this->locale->run();
    $this->security->run();
    
    if (!$this->initProject()) {
      return;
    }
    
    $this->initLinks();
    $this->initTextRegex();
    
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
    $this->addLink(new Link(T_("Home"), null));
    
    if ($this->project != null) {
      $this->addLink(new Link(T_("Issues"), "issues/"));
    }
    
    if (!$this->security->isLoggedIn()) {
      $this->addLink(new Link(T_("Login"), $this->linkProvider->forLogin(false)));
    }
    else {
      if ($this->security->userIsType(\Spit\UserType::Admin)) {
        $this->addLink(new Link(T_("Admin"), "admin/"));
      }
    }
  }
  
  private function initTextRegex() {
    $comment = new \stdClass;
    $comment->find = "/comment #(\d+)/";
    $comment->replace = sprintf("comment [#$1](#c$1)", $this->getProjectRoot(false));
    array_push($this->textRegex, $comment);
    
    $issue = new \stdClass;
    $issue->find = "/issue #(\d+)/";
    $issue->replace = sprintf("issue [#$1](%s/issue/details/$1/)", $this->getProjectRoot(false));
    array_push($this->textRegex, $issue);
  }
  
  private function initProject() {
    $dataStore = new DataStores\ProjectDataStore;
    
    if (!$this->isSingleProject()) {
      if (in_array($this->path->get(0), $this->globalLinks)) {
        // if no project is set, the index controller will
        // just show project links.
        return true;
      }
      
      $this->project = $dataStore->getByName($this->path->get(0));
      
      $this->addLink(new Link(T_("Projects"), $this->getRoot(), true));
      
      // offset the path so that pages wanting index 1 get 2,
      // since the part at 0 is now the project name.
      $this->path->setOffset(1);
    }
    else {
      $this->project = $dataStore->getByName($this->settings->site->singleProject);
    }
    
    if ($this->project == null) {
      $this->showError(HttpCode::NotFound);
      return false;
    }
    
    if (!$this->project->isPublic && !$this->userIsMember($this->project)) {
      $this->showError(HttpCode::Forbidden);
      return false;
    }
    
    return true;
  }
  
  private function userIsMember($project) {
    if (!$this->security->isLoggedIn()) {
      return false;
    }
    
    $dataStore = new DataStores\MemberDataStore;
    $members = $dataStore->getForProject($project->id);
    foreach ($members as $member) {
      if ($member->userId == $this->security->user->id) {
        return true;
      }
    }
    
    return false;
  }
  
  public function showError($code) {
    $this->controller = $this->error;
    $this->error->show($code);
  }
  
  public function getRoot($trailingSlash = true) {
    $scriptName = $_SERVER['SCRIPT_NAME'];
    $pos = strrpos($scriptName, "/");
    if (!$trailingSlash && substr($scriptName, 0, 1) == "/") {
      return substr($scriptName, 1, $pos);
    }
    return substr($scriptName, 0, $pos + 1);
  }
  
  public function getProjectRoot($trailingSlash = true) {
    if ($this->isSingleProject() || ($this->project == null)) {
      return $this->getRoot($trailingSlash);
    }
    else {
      return $this->getRoot() . $this->project->name . ($trailingSlash ? "/" : "");
    }
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
