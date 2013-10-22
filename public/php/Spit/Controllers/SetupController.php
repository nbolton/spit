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

require_once "php/Spit/Models/Priority.php";
require_once "php/Spit/Models/Tracker.php";
require_once "php/Spit/Models/Category.php";

class SetupController extends Controller {
  
  public function __construct($app) {
    $this->app = $app;
    $this->siteWide = true;
  }
  
  public function run() {
  
    if ($this->isPost()) {
      $this->handlePost();
      return;
    }
    
    $this->showView("setup", T_("Setup"));
  }
  
  private function handlePost() {
    
    $projects = array();
    $trackers = array();
    $statuses = array();
    $priorities = array();
    $categories = array();
    
    $projectLines = preg_split("/[\r\n]/", $_POST["projects"], null, PREG_SPLIT_NO_EMPTY);
    foreach ($projectLines as $line) {
      $nameAndTitle = preg_split("/:\s*/", $line, 2, PREG_SPLIT_NO_EMPTY);
      
      if (count($nameAndTitle) < 2) {
        $this->app->showBasicMessage(
          T_("Error: Project info line does not have name and title separated by colon (:)."));
        return;
      }
      
      $project = new \Spit\Models\Project;
      $project->name = $nameAndTitle[0];
      $project->title = $nameAndTitle[1];
      $project->isPublic = true;
      array_push($projects, $project);
    }
    
    if (count($projects) == 0) {
      $this->app->showBasicMessage(T_("Error: No project names were provided."));
      return;
    }
    
    $order = 0;
    $trackerParts = preg_split("/,\s*/", $_POST["trackers"], null, PREG_SPLIT_NO_EMPTY);
    foreach ($trackerParts as $part) {
      $tracker = new \Spit\Models\Tracker;
      $tracker->name = $part;
      
      // only temporary until we improve the setup form.
      $tracker->order = $order++;
      
      array_push($trackers, $tracker);
    }
    
    $order = 0;
    $priorityParts = preg_split("/,\s*/", $_POST["priorities"], null, PREG_SPLIT_NO_EMPTY);
    foreach ($priorityParts as $part) {
      $priority = new \Spit\Models\Priority;
      $priority->name = $part;
      
      // only temporary until we improve the setup form.
      $priority->order = $order++;
      $priority->isDefault = $part == "Normal";
      
      array_push($priorities, $priority);
    }
    
    $order = 0;
    $statusParts = preg_split("/,\s*/", $_POST["statuses"], null, PREG_SPLIT_NO_EMPTY);
    foreach ($statusParts as $part) {
      $status = new \Spit\Models\Status;
      $status->name = $part;
      
      // only temporary until we improve the setup form.
      $status->order = $order++;
      $status->isDefault = $part == "New";
      $status->closed = in_array($part, array("Fixed", "Invalid", "Duplicate"));
      
      array_push($statuses, $status);
    }
    
    $order = 0;
    $priorityParts = preg_split("/,\s*/", $_POST["priorities"], null, PREG_SPLIT_NO_EMPTY);
    foreach ($priorityParts as $part) {
      $priority = new \Spit\Models\Priority;
      $priority->name = $part;
      
      // only temporary until we improve the setup form.
      $priority->order = $order++;
      $priority->isDefault = $part == "Normal";
      
      array_push($priorities, $priority);
    }
    
    $categoryParts = preg_split("/,\s*/", $_POST["categories"], null, PREG_SPLIT_NO_EMPTY);
    foreach ($categoryParts as $part) {
      $category = new \Spit\Models\Category;
      $category->name = $part;
      array_push($categories, $category);
    }
    
    // make the current user an admin since they're running the setup.
    $user = $this->app->security->user;
    $user->typeMask =
      \Spit\UserType::Newbie |
      \Spit\UserType::Member |
      \Spit\UserType::Manager |
      \Spit\UserType::Admin;
    
    $userDS = new \Spit\DataStores\UserDataStore;
    $projectDS = new \Spit\DataStores\ProjectDataStore;
    $trackerDS = new \Spit\DataStores\TrackerDataStore;
    $priorityDS = new \Spit\DataStores\PriorityDataStore;
    $statusDS = new \Spit\DataStores\StatusDataStore;
    $categoryDS = new \Spit\DataStores\CategoryDataStore;
    
    $userDS->update($user);
    $projectDS->insertMany($projects);
    $trackerDS->insertMany($trackers);
    $priorityDS->insertMany($priorities);
    $statusDS->insertMany($statuses);
    $categoryDS->insertMany($categories);
    
    // refresh page. since we are only sent here when there are no projects,
    // this should not cause an infinite loop. though there is still a risk
    // if we change the code outside this class, so maybe there is a better
    // approach to this...
    header("Location: .");
    exit;
  }
}

?>
