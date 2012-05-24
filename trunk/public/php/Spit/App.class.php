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

require "Controllers/ControllerProvider.class.php";
require "Settings.class.php";
require "Locale.class.php";

class App {
  
  const DEFAULT_PAGE = "home";
  
  public function __construct() {
    $this->settings = new Settings;
    $this->locale = new Locale;
    $this->root = "";
    $this->theme = $this->root . "/theme/default";
  }
  
  public function run() {
  
    $this->locale->run();
    
    $pathString = isset($_GET["path"]) ? $_GET["path"] : "";
    $path = preg_split('@/@', $pathString, NULL, PREG_SPLIT_NO_EMPTY);
  
    $provider = new Controllers\ControllerProvider;
    $controller = $provider->get($path);
    $controller->app = $this;
    $controller->run($path);
  }
}

?>
