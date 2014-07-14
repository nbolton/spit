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

class LoginController extends Controller {
  
  public function __construct() {
    $this->siteWide = true;
  }
  
  public function run() {
    switch ($this->getPathPart(1)) {
      case "": $this->runIndex(); break;
      default: $this->showError(404); break;
    }
  }
  
  private function runIndex() {
    $data["failed"] = false;
    $data["fromArg"] = isset($_GET["from"]) ? "&from=" . $_GET["from"] : "";
    
    if (isset($_GET["start"])) {
      $this->app->security->startLogin();
      return;
    }
    elseif (isset($_GET["code"])) {
      if ($this->app->security->finishLogin()) {
        exit;
      }
      else {
        $data["failed"] = true;
      }
    }

    $this->showView("login", T_("Login"), $data);
  }
}

?>
