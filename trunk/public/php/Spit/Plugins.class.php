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

class Plugins {

  public function __construct($app) {
    $this->app = $app;
    $this->plugins = array();
  }

  public function load() {
    $handler = opendir("php/plugins/");
    while ($file = readdir($handler)) {
      if ($file == ".." || $file == ".")
        continue;
      
      require sprintf("php/plugins/%s/%s.class.php", $file, $file);
      $plugin = new $file($this->app);
      $plugin->name = $file;
      array_push($this->plugins, $plugin);
    }
  }
}

?>
