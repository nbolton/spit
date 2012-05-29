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
  const MASTER_VIEW = "master";

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
    $master = self::MASTER_VIEW;
    
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
  
  public function applyFormValues($object) {
    $diffs = new \stdClass;
    foreach ($_POST as $k => $v) {
      if (isset($object->$k)) {
        $diff = $this->diff((string)$v, (string)$object->$k);
        if ($diff != null) {
          $diffs->$k = $diff;
        }
      }
      $object->$k = $v;
    }
    return $diffs;
  }
  
  public function diff($old, $new) {
    if ($old == $new) {
      return null;
    }
    // TODO: improve for large bodies of text.
    return sprintf("-%s\n+%s", $new, $old);
  }
  
  public function getValue($object, $field) {
    return $object->$field;
  }
  
  public function isJsonRequest() {
    return isset($_GET["format"]) && $_GET["format"] == "json";
  }
  
  public function getJson($data) {
    return json_encode(array(
      "data" => $data,
      "stats" => array(
        "queries" => $this->app->queryCount,
        "loadTime" => $this->app->getLoadTime()
      )
    ));
  }
  
  public function getDateString($date) {
    return $date->format($this->app->settings->site->dateTimeFormat);
  }
}

?>
