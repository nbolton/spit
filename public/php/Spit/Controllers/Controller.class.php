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
use \Spit\TitleMode as TitleMode;

class Controller {

  const DEFAULT_VIEW_DIR = "php/Spit/Views/";
  const MASTER_VIEW = "master";

  public $app;
  public $viewDir = self::DEFAULT_VIEW_DIR;
  public $useMarkdown = false;
  public $siteWide = false;
  
  protected function showView($view, $title = "", $data = array(), $titleMode = TitleMode::Prefix) {
    foreach ($data as $k => $v) {
      $$k = $v;
    }
    
    $app = $this->app;
    $self = $this;
    
    if ($titleMode == TitleMode::Prefix) {
      $fullTitle = $app->getSiteTitle() . (($title != "") ? " - " . $title : "");
    }
    elseif ($titleMode == TitleMode::Affix) {
      $fullTitle = (($title != "") ? $title . " - " : "") . $app->getSiteTitle();
    }
    else {
      $fullTitle = $title;
    }
    
    $content = $this->viewDir . $view . ".php";
    
    if (!file_exists($content)) {
      throw new Exception("view not found at: " . $content);
    }
    
    require self::DEFAULT_VIEW_DIR . self::MASTER_VIEW . ".php";
  }
  
  protected function showError($number) {
    $this->app->error->show($number);
  }
  
  protected function getPostValue($name) {
    return isset($_POST[$name]) ? $_POST[$name] : "";
  }
  
  protected function auth($userType, $passive = false) {
    return $this->app->security->auth($userType, $passive);
  }
  
  public function getViewStyle($view) {
    $path = sprintf("%s/style/%s.css", $this->app->getThemeDir(), $view);
    if (file_exists($path)) {
      return sprintf(
        "<link rel=\"stylesheet\" type=\"text/css\" href=\"%s/%s\" />\n",
        $this->app->getRoot(), $path);
    }
  }
  
  public function getViewScript($view) {
    $path = sprintf("js/%s.js", $view);
    if (file_exists($path)) {
      return sprintf(
        "<script type=\"text/javascript\" src=\"%s/%s\"></script>\n",
        $this->app->getRoot(), $path);
    }
  }
  
  public function getPathPart($index) {
    return $this->app->path->get($index);
  }
  
  public function isPost() {
    return $_SERVER["REQUEST_METHOD"] == "POST";
  }
  
  public function applyFormValues($object, $index = null, $doDiff = true) {
    
    if ($index == null) {
      $values = $_POST;
    }
    else {
      $values = $_POST[$index];
    }
    
    $diffs = array();
    foreach ($values as $k => $v) {
      if ($doDiff) {
        $old = isset($object->$k) && $object->$k != "" ? (string)$object->$k : null;
        $new = $v != "" ? (string)$v : null;
        
        if ($old != $new) {
          $diff = new \stdClass;
          $diff->oldValue = $old;
          $diff->newValue = $new;
          $diffs[$k] = $diff;
        }
      }
      $object->$k = $v;
    }
    
    return $diffs;
  }
  
  public function getValue($object, $field) {
    return $object->$field;
  }
  
  public function isJsonGet() {
    return (isset($_GET["format"]) && $_GET["format"] == "json");
  }
  
  public function isJsonPost() {
    return (isset($_POST["format"]) && $_POST["format"] == "json");
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
  
  public function formatDate($date) {
    return $this->app->dateFormatter->format($date);
  }
}

?>
