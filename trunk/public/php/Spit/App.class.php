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
require "DateFormatter.class.php";
require "SessionManager.class.php";

require "Controllers/ControllerProvider.class.php";
require "Controllers/ErrorController.class.php";
require "Controllers/SetupController.class.php";

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
require "DataStores/QueryDataStore.class.php";

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
require "Models/Query.class.php";

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
    $this->checkFiles();
    
    try {
      $this->init();
    }
    catch (\Exception $ex) {
      $this->showBasicMessage(sprintf(T_("Fatal error: <code>%s</code>"), $ex->getMessage()));
      exit;
    }
  }
  
  private function init() {
  
    $this->settings = new Settings;
    $this->controllers = new Controllers\ControllerProvider;
    $this->locale = new Locale;
    $this->plugins = new Plugins($this);
    $this->security = new Security($this);
    $this->error = new Controllers\ErrorController($this);
    $this->setup = new Controllers\SetupController($this);
    $this->path = new Path;
    $this->linkProvider = new LinkProvider($this);    
    $this->dateFormatter = new DateFormatter($this->settings);
    $this->sessionManager = new SessionManager($this->settings);
    
    // default user level needed to create new issues.
    $this->newIssueUserType = UserType::Newbie;
    
    $this->links = array();
    $this->textRegex = array();
  }
  
  public function run() {
  
    if (!$this->userIsBot()) {
      $this->sessionManager->start();
    }

    $this->locale->run();
    $this->security->run();
    $this->plugins->load();
    
    if (!$this->initProject()) {
      return;
    }
    
    $this->initLinks();
    $this->initTextRegex();
    
    $this->controller = $this->controllers->find($this->path->get(0));
    if ($this->controller == null) {
      $this->showError(HttpCode::NotFound);
      return;
    }
    
    $this->controller->app = $this;
    $this->controller->run();
  }
  
  private function checkFiles() {  
    if (!file_exists(Settings::$filename)) {
      $this->showBasicMessage(
        T_("Please copy <code>settings.ini.example</code> to <code>settings.ini</code>"));
      exit;
    }
    
    if (!file_exists(".htaccess")) {
      $this->showBasicMessage(
        T_("Please copy <code>.htaccess.example</code> to <code>.htaccess</code>"));
      exit;
    }
  }
  
  public function showBasicMessage($text) {
    echo "<style>code { color: blue; }</style><p>$text</p>";
  }
  
  private function initLinks() {
    $this->addLink(new Link(T_("Home"), null, \Spit\LinkType::Project));
    $this->addLink(new Link(T_("Issues"), "issues/", \Spit\LinkType::Project));
    $this->addLink(new Link(T_("Roadmap"), "roadmap/", \Spit\LinkType::Project));
    
    if (!$this->security->isLoggedIn()) {
      $this->addLink(new Link(T_("Login"), $this->linkProvider->forLogin(false), \Spit\LinkType::Site));
    }
    else {
      if ($this->security->userIsType(\Spit\UserType::Admin)) {
        $this->addLink(new Link(T_("Admin"), "admin/", \Spit\LinkType::Site));
      }
    }
  }
  
  private function initTextRegex() {
    $comment = new \stdClass;
    $comment->find = "/(comment) #(\d+)/i";
    $comment->replace = sprintf("$1 [#$2](#c$2)", $this->getProjectRoot());
    array_push($this->textRegex, $comment);
    
    $issue = new \stdClass;
    $issue->find = "/(issue|bug|feature|task) #(\d+)/i";
    $issue->replace = sprintf("$1 [#$2](%s/issues/details/$2/)", $this->getProjectRoot());
    array_push($this->textRegex, $issue);
  }
  
  private function initProject() {
    $dataStore = new DataStores\ProjectDataStore;
    
    // if there are no projects at all, assume the database is empty.
    // only do this if the user isn't trying to login.
    if (($dataStore->getCount() == 0) && ($this->path->get(0) != "login")) {
      $this->controller = $this->setup;
      $this->controller->run();
      return;
    }
    
    if (!$this->isSingleProject()) {
      
      $this->addLink(new Link(T_("Projects"), null, \Spit\LinkType::Site));
      
      if ($this->controllers->isSiteWide($this->path->get(0))) {
        // if the first part of the url is the name of a controller
        // which is site-wide, then don't bother looking for a project
        // of that name -- just use the controller.
        return true;
      }
      
      $this->project = $dataStore->getByName($this->path->get(0));
      
      // offset the path so that pages wanting index 1 get 2,
      // since the part at 0 is now the project name.
      $this->path->setOffset(1);
    }
    else if (isset($this->settings->site->singleProject)) {
      $this->project = $dataStore->getByName($this->settings->site->singleProject);
    }
    
    if ($this->project == null) {
      if (isset($this->settings->site->singleProject)) {
        $this->showErrorMessage(
          sprintf(T_("Project does not exist: %s"),
          $this->settings->site->singleProject));
      }
      else {
        $this->showErrorMessage(T_("No project selected and not in single project mode."));
      }
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
    $this->error->showCode($code);
  }
  
  public function showErrorMessage($message) {
    $this->controller = $this->error;
    $this->error->showMessage($message);
  }
  
  public function getRoot() {
    $scriptName = $_SERVER['SCRIPT_NAME'];
    $lastSlash = strrpos($scriptName, "/");
    return substr($scriptName, 0, $lastSlash);
  }
  
  public function getProjectRoot() {
    if ($this->isSingleProject() || ($this->project == null)) {
      return $this->getRoot();
    }
    else {
      return sprintf("%s/%s", $this->getRoot(), $this->project->name);
    }
  }
  
  public function getThemeDir() {
    return "theme/default";
  }
  
  public function getThemeFile($file) {
    return sprintf("%s/%s/%s", $this->getRoot(), $this->getThemeDir(), $file);
  }
  
  public function getImage($image) {
    return $this->getThemeFile("image/" . $image);
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
    switch ($link->type) {
      case \Spit\LinkType::External: return $link->href;
      case \Spit\LinkType::Site: return sprintf("%s/%s", $this->getRoot(), $link->href);
      case \Spit\LinkType::Project: return sprintf("%s/%s", $this->getProjectRoot(), $link->href);
      default: return null;
    }
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
  
  public function getLinks() {
    $result = array();
    foreach ($this->links as $link) {
      if ($this->project != null) {
        // if in project, show all links.
        array_push($result, $link);
      }
      elseif ($link->type != \Spit\LinkType::Project) {
        // if no project, hide project links.
        array_push($result, $link);
      }
    }
    return $result;
  }
  
  public function userIsBot() {
    return preg_match("/(bot|spider)/", $_SERVER["HTTP_USER_AGENT"]) != 0;
  }
}

?>
