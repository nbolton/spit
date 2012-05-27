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

use Exception;

class Controller {

  const DEFAULT_VIEW_DIR = "php/Spit/Views/";

  public $app;
  public $viewDir = self::DEFAULT_VIEW_DIR;
  
  protected function showView($view, $title = "", $data = array()) {
    foreach ($data as $k => $v) {
      $$k = $v;
    }
    
    $app = $this->app;
    $self = $this;
    $fullTitle = $app->getSiteTitle() . (($title != "") ? " - " . $title : "");
    $content = $this->viewDir . $view . ".php";
    $master = $app->settings->layout->masterView;
    
    if (!file_exists(sprintf("%s/%s", $_SERVER["DOCUMENT_ROOT"], $content))) {
      throw new Exception("view not found at: " . $content);
    }
    
    require self::DEFAULT_VIEW_DIR . $master . ".php";
  }
  
  protected function showError($number) {
    $this->app->error->show($number);
  }
  
  protected function getPostValue($name) {
    return isset($_POST[$name]) ? $_POST[$name] : "";
  }
  
  public function getViewStyle($view) {
    $path = sprintf("%s/style/%s.css", $this->app->getThemeRoot(), $view);
    if (file_exists($_SERVER["DOCUMENT_ROOT"] . $path)) {
      return sprintf("<link rel=\"stylesheet\" type=\"text/css\" href=\"%s\" />\n", $path);
    }
  }
  
  public function getViewScript($view) {
    $path = sprintf("%sjs/%s.js", $this->app->getProjectRoot(), $view);
    if (file_exists($_SERVER["DOCUMENT_ROOT"] . $path)) {
      return sprintf("<script type=\"text/javascript\" src=\"%s\"></script>\n", $path);
    }
  }
  
  public function getPathPart($index) {
    return $this->app->path->get($index);
  }
  
  public function isPost() {
    return $_SERVER["REQUEST_METHOD"] == "POST";
  }
  
  public function setFormValues($object) {
    foreach ($_POST as $k => $v) {
      $object->$k = $v;
    }
  }
  
  public function getValue($object, $field) {
    return $object->$field;
  }
}

?>
