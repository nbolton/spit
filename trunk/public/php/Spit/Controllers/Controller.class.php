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

class Controller {

  const DEFAULT_VIEW_DIR = "php/Spit/Views/";

  public $app;
  public $viewDir = self::DEFAULT_VIEW_DIR;
  
  protected function showView($view, $title = "", $data = array()) {
    foreach ($data as $k => $v) {
      $$k = $v;
    }
    
    $app = $this->app;
    $fullTitle = $app->settings->site->title . (($title != "") ? " - " . $title : "");
    $content = $this->viewDir . $view . ".php";
    $master = $app->settings->layout->masterView;
    
    require self::DEFAULT_VIEW_DIR . $master . ".php";
  }
  
  protected function showError($number) {
    $this->app->error->show($number);
  }
  
  protected function getPostValue($name) {
    return isset($_POST[$name]) ? $_POST[$name] : "";
  }
  
  public function getViewStyle($view) {
    $path = sprintf("%s/%s.css", $this->app->theme, $view);
    if (file_exists($_SERVER["DOCUMENT_ROOT"] . $path)) {
      return sprintf("<link rel=\"stylesheet\" type=\"text/css\" href=\"%s\" />\n", $path);
    }
  }
  
  public function getViewScript($view) {
    $path = sprintf("%sjs/%s.js", $this->app->root, $view);
    if (file_exists($_SERVER["DOCUMENT_ROOT"] . $path)) {
      return sprintf("<script type=\"text/javascript\" src=\"%s\"></script>\n", $path);
    }
  }
  
  public function getPathPart($index) {
    return $this->app->path->get($index);
  }
}

?>
