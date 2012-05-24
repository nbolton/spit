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

class IssuesController extends Controller {
  
  public function run($path) {
    
    if (count($path) == 1) {
      $this->showView("issues/index");
    }
    else {
      switch (strtolower($path[1])) {
        case "new": $this->runNew(); break;
      }
    }
  }
  
  private function runNew() {
    $data["editorTitle"] = T_("New Issue");
    $this->showView("issues/editor", $data);
  }
}

?>
