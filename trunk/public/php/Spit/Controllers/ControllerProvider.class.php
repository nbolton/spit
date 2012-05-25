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

require "Controller.class.php";
require "IndexController.class.php";
require "IssuesController.class.php";

use Exception;

class ControllerProvider {

  public function __construct() {
    $this->controllers = array();
    $this->map("", new IndexController);
    $this->map("issues", new IssuesController);
  }
  
  public function map($name, $controller) {
    $this->controllers[$name] = $controller;
  }

  public function get($path) {
    $name = count($path) != 0 ? $path[0] : "";
    if (array_key_exists($name, $this->controllers)) {
      return $this->controllers[$name];
    }
    throw new Exception("controller not found for: " . $name);
  }
}

?>
