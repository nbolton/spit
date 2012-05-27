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

class Path {

  public function __construct() {
    $this->pathString = isset($_GET["path"]) ? $_GET["path"] : "";
    $this->parts = preg_split('@/@', $this->pathString, NULL, PREG_SPLIT_NO_EMPTY);
  }
  
  public function get($index) {
    if ($index >= count($this->parts)) {
      return "";
    }
    
    return $this->parts[$index];
  }
  
  public function toString() {
    return $this->pathString;
  }
}

?>
