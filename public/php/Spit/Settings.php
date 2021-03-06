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

class SettingsSection { }

class Settings {
  
  public static $instance;
  public static $filename = "settings.ini";
  
  public function __construct() {
    self::$instance = $this;
    
    $ini = parse_ini_file(self::$filename, true);
    foreach ($ini as $section => $values) {
      $this->$section = new SettingsSection;
      foreach ($values as $k => $v) {
        $this->$section->$k = $v;
      }
    }
  }
}

?>
